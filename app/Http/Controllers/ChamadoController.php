<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChamadoRequest;
use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\ChamadoHistorico;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChamadoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $chamados = Chamado::query()
            ->visivelPara($user)
            ->with(['categoria', 'grupo', 'solicitante', 'tecnico'])
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = trim($request->busca);
                $q->where(fn ($q) => $q->where('titulo', 'like', "%{$busca}%")
                    ->orWhere('descricao', 'like', "%{$busca}%")
                    ->orWhere('numero', 'like', '%'.strtoupper($busca).'%'));
            })
            // Filas prontas do menu lateral.
            ->when($request->fila === 'meus', fn ($q) => $q->where('tecnico_id', $user->id)->pendentes())
            ->when($request->fila === 'grupo', fn ($q) => $q->where('grupo_id', $user->grupo_id)->pendentes())
            ->when($request->fila === 'sem_atribuicao', fn ($q) => $q->whereNull('tecnico_id')->pendentes())
            ->when($request->fila === 'pendentes', fn ($q) => $q->pendentes())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('prioridade'), fn ($q) => $q->where('prioridade', $request->prioridade))
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('grupo_id'), fn ($q) => $q->where('grupo_id', $request->grupo_id))
            ->when($request->filled('tecnico_id'), function ($q) use ($request) {
                $request->tecnico_id === 'nenhum'
                    ? $q->whereNull('tecnico_id')
                    : $q->where('tecnico_id', $request->tecnico_id);
            })
            ->orderBy($this->colunaOrdenacao($request), $request->direcao === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('chamados.index', [
            'chamados' => $chamados,
            'categorias' => Categoria::orderBy('nome')->get(),
            'grupos' => Grupo::orderBy('nome')->get(),
            'tecnicos' => $this->tecnicos(),
        ]);
    }

    public function create(): View
    {
        return view('chamados.create', ['categorias' => Categoria::orderBy('nome')->get()]);
    }

    public function store(ChamadoRequest $request): RedirectResponse
    {
        $chamado = DB::transaction(function () use ($request) {
            $chamado = Chamado::create($request->validated() + [
                'solicitante_id' => $request->user()->id,
                'status' => 'aberto',
                // Todo incidente novo cai primeiro na fila do Service Desk.
                'grupo_id' => Grupo::where('nome', 'Service Desk')->value('id'),
            ]);

            $chamado->historicos()->create([
                'user_id' => $request->user()->id,
                'tipo' => 'comentario',
                'status_novo' => 'aberto',
                'comentario' => 'Incidente registrado.',
            ]);

            return $chamado;
        });

        return redirect()->route('chamados.show', $chamado)
            ->with('sucesso', "Incidente {$chamado->numero} registrado com sucesso.");
    }

    public function show(Request $request, Chamado $chamado): View
    {
        Gate::authorize('ver-chamado', $chamado);

        $chamado->load(['categoria', 'grupo', 'solicitante', 'tecnico']);

        return view('chamados.show', [
            'chamado' => $chamado,
            'atividades' => $chamado->historicos()->visivelPara($request->user())->with('user')->get(),
            'tecnicos' => $this->tecnicos(),
            'grupos' => Grupo::orderBy('nome')->get(),
        ]);
    }

    public function edit(Chamado $chamado): View
    {
        Gate::authorize('editar-chamado', $chamado);

        return view('chamados.edit', [
            'chamado' => $chamado,
            'categorias' => Categoria::orderBy('nome')->get(),
        ]);
    }

    public function update(ChamadoRequest $request, Chamado $chamado): RedirectResponse
    {
        Gate::authorize('editar-chamado', $chamado);

        $prioridadeAnterior = $chamado->prioridade;
        $chamado->update($request->validated());

        if ($prioridadeAnterior !== $chamado->prioridade) {
            $chamado->historicos()->create([
                'user_id' => $request->user()->id,
                'tipo' => 'comentario',
                'status_anterior' => $chamado->status,
                'status_novo' => $chamado->status,
                'comentario' => 'Prioridade alterada de '.(Chamado::PRIORIDADES[$prioridadeAnterior] ?? $prioridadeAnterior)
                    ." para {$chamado->prioridade_label}.",
            ]);
        }

        return redirect()->route('chamados.show', $chamado)->with('sucesso', 'Incidente atualizado com sucesso.');
    }

    public function destroy(Chamado $chamado): RedirectResponse
    {
        Gate::authorize('admin');

        $chamado->delete();

        return redirect()->route('chamados.index')->with('sucesso', "Incidente {$chamado->numero} removido.");
    }

    /** Atendimento: status, grupo, técnico e nota de trabalho/comentário, registrando histórico. */
    public function atualizarStatus(Request $request, Chamado $chamado): RedirectResponse
    {
        Gate::authorize('atender-chamados');

        $dados = $request->validate([
            'status' => ['required', Rule::in(array_keys(Chamado::STATUS))],
            'grupo_id' => ['nullable', Rule::exists('grupos', 'id')],
            'tecnico_id' => ['nullable', Rule::exists('users', 'id')->whereIn('perfil', ['tecnico', 'admin'])],
            'tipo' => ['nullable', Rule::in(array_keys(ChamadoHistorico::TIPOS))],
            'comentario' => [
                Rule::requiredIf(in_array($request->status, Chamado::FINALIZADOS) && ! $chamado->finalizado()),
                'nullable', 'string', 'max:2000',
            ],
        ], [
            'comentario.required' => 'Informe as notas de resolução para resolver, fechar ou cancelar o incidente.',
        ], ['grupo_id' => 'grupo de atribuição', 'tecnico_id' => 'técnico', 'comentario' => 'comentário']);

        $statusAnterior = $chamado->status;
        $grupoAnterior = $chamado->grupo_id;
        $tecnicoAnterior = $chamado->tecnico_id;
        $grupoNovo = $dados['grupo_id'] ?? null;
        $tecnicoNovo = $dados['tecnico_id'] ?? null;

        // Ao assumir pelo botão, o incidente vai para o grupo do técnico.
        if ($tecnicoNovo && ! $grupoNovo) {
            $grupoNovo = User::find($tecnicoNovo)->grupo_id ?? $grupoAnterior;
        }

        if ($statusAnterior === $dados['status'] && $grupoAnterior == $grupoNovo
            && $tecnicoAnterior == $tecnicoNovo && empty($dados['comentario'])) {
            return back()->with('erro', 'Nenhuma alteração informada.');
        }

        DB::transaction(function () use ($chamado, $dados, $statusAnterior, $grupoAnterior, $grupoNovo, $tecnicoAnterior, $tecnicoNovo, $request) {
            $chamado->update([
                'status' => $dados['status'],
                'grupo_id' => $grupoNovo,
                'tecnico_id' => $tecnicoNovo,
                'resolvido_em' => in_array($dados['status'], Chamado::FINALIZADOS) ? ($chamado->resolvido_em ?? now()) : null,
            ]);

            $alteracoes = [];
            if ($grupoAnterior != $grupoNovo) {
                $alteracoes[] = 'Grupo de atribuição: '.($grupoNovo ? Grupo::find($grupoNovo)->nome : 'nenhum').'.';
            }
            if ($tecnicoAnterior != $tecnicoNovo) {
                $alteracoes[] = 'Atribuído a: '.($tecnicoNovo ? User::find($tecnicoNovo)->name : 'ninguém').'.';
            }

            $texto = $dados['comentario'] ?? null;
            $houveMudanca = $statusAnterior !== $dados['status'] || $alteracoes;

            // Mudanças de campos ficam sempre visíveis ao solicitante;
            // só o texto de uma nota de trabalho é interno.
            if (($dados['tipo'] ?? 'nota') === 'nota' && $texto) {
                if ($houveMudanca) {
                    $this->registrar($chamado, $request, 'comentario', $statusAnterior, implode(' ', $alteracoes));
                }
                $this->registrar($chamado, $request, 'nota', $dados['status'], $texto);
            } else {
                $this->registrar($chamado, $request, 'comentario', $statusAnterior, trim(implode(' ', $alteracoes).' '.$texto));
            }
        });

        return redirect()->route('chamados.show', $chamado)->with('sucesso', "Incidente {$chamado->numero} atualizado.");
    }

    /** Comentário adicional do solicitante (ou de quem pode ver o incidente). */
    public function comentar(Request $request, Chamado $chamado): RedirectResponse
    {
        Gate::authorize('ver-chamado', $chamado);

        $dados = $request->validate([
            'comentario' => ['required', 'string', 'max:2000'],
        ], [], ['comentario' => 'comentário']);

        $chamado->historicos()->create([
            'user_id' => $request->user()->id,
            'tipo' => 'comentario',
            'status_anterior' => $chamado->status,
            'status_novo' => $chamado->status,
            'comentario' => $dados['comentario'],
        ]);

        $chamado->touch();

        return redirect()->route('chamados.show', $chamado)->with('sucesso', 'Comentário adicionado.');
    }

    private function registrar(Chamado $chamado, Request $request, string $tipo, string $statusAnterior, ?string $texto): void
    {
        $chamado->historicos()->create([
            'user_id' => $request->user()->id,
            'tipo' => $tipo,
            'status_anterior' => $statusAnterior,
            'status_novo' => $chamado->status,
            'comentario' => $texto ?: null,
        ]);
    }

    private function colunaOrdenacao(Request $request): string
    {
        $permitidas = ['numero', 'titulo', 'prioridade', 'status', 'created_at', 'updated_at'];

        return in_array($request->ordem, $permitidas) ? $request->ordem : 'created_at';
    }

    private function tecnicos()
    {
        return User::whereIn('perfil', ['tecnico', 'admin'])->with('grupo')->orderBy('name')->get();
    }
}
