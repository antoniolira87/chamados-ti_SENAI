<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChamadoRequest;
use App\Models\Categoria;
use App\Models\Chamado;
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
        $chamados = Chamado::query()
            ->visivelPara($request->user())
            ->with(['categoria', 'solicitante', 'tecnico'])
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = $request->busca;
                $q->where(fn ($q) => $q->where('titulo', 'like', "%{$busca}%")
                    ->orWhere('descricao', 'like', "%{$busca}%")
                    ->orWhere('id', ltrim($busca, '#')));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('prioridade'), fn ($q) => $q->where('prioridade', $request->prioridade))
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('tecnico_id'), function ($q) use ($request) {
                $request->tecnico_id === 'nenhum'
                    ? $q->whereNull('tecnico_id')
                    : $q->where('tecnico_id', $request->tecnico_id);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('chamados.index', [
            'chamados' => $chamados,
            'categorias' => Categoria::orderBy('nome')->get(),
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
            ]);

            $chamado->historicos()->create([
                'user_id' => $request->user()->id,
                'status_novo' => 'aberto',
                'comentario' => 'Chamado aberto.',
            ]);

            return $chamado;
        });

        return redirect()->route('chamados.show', $chamado)
            ->with('sucesso', "Chamado #{$chamado->id} aberto com sucesso.");
    }

    public function show(Chamado $chamado): View
    {
        Gate::authorize('ver-chamado', $chamado);

        $chamado->load(['categoria', 'solicitante', 'tecnico', 'historicos.user']);

        return view('chamados.show', [
            'chamado' => $chamado,
            'tecnicos' => $this->tecnicos(),
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

        $chamado->update($request->validated());

        return redirect()->route('chamados.show', $chamado)->with('sucesso', 'Chamado atualizado com sucesso.');
    }

    public function destroy(Chamado $chamado): RedirectResponse
    {
        Gate::authorize('admin');

        $chamado->delete();

        return redirect()->route('chamados.index')->with('sucesso', "Chamado #{$chamado->id} removido.");
    }

    /** Mudança de status / atribuição de técnico, registrando histórico. */
    public function atualizarStatus(Request $request, Chamado $chamado): RedirectResponse
    {
        Gate::authorize('atender-chamados');

        $dados = $request->validate([
            'status' => ['required', Rule::in(array_keys(Chamado::STATUS))],
            'tecnico_id' => ['nullable', Rule::exists('users', 'id')->whereIn('perfil', ['tecnico', 'admin'])],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ], [], ['tecnico_id' => 'técnico', 'comentario' => 'comentário']);

        $statusAnterior = $chamado->status;
        $tecnicoAnterior = $chamado->tecnico_id;
        $tecnicoNovo = $dados['tecnico_id'] ?? null;

        if ($statusAnterior === $dados['status'] && $tecnicoAnterior == $tecnicoNovo && empty($dados['comentario'])) {
            return back()->with('erro', 'Nenhuma alteração informada.');
        }

        DB::transaction(function () use ($chamado, $dados, $statusAnterior, $tecnicoAnterior, $tecnicoNovo, $request) {
            $finalizado = in_array($dados['status'], ['resolvido', 'fechado']);

            $chamado->update([
                'status' => $dados['status'],
                'tecnico_id' => $tecnicoNovo,
                'resolvido_em' => $finalizado ? ($chamado->resolvido_em ?? now()) : null,
            ]);

            $comentario = $dados['comentario'] ?? null;
            if ($tecnicoAnterior != $tecnicoNovo) {
                $nome = $tecnicoNovo ? User::find($tecnicoNovo)->name : 'ninguém';
                $comentario = trim("Técnico responsável: {$nome}. ".($comentario ?? ''));
            }

            $chamado->historicos()->create([
                'user_id' => $request->user()->id,
                'status_anterior' => $statusAnterior,
                'status_novo' => $dados['status'],
                'comentario' => $comentario,
            ]);
        });

        return redirect()->route('chamados.show', $chamado)->with('sucesso', 'Chamado atualizado.');
    }

    private function tecnicos()
    {
        return User::whereIn('perfil', ['tecnico', 'admin'])->orderBy('name')->get();
    }
}
