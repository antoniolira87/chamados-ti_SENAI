@extends('layouts.app')
@section('title', $chamado->numero)
@section('content')
@php $atende = auth()->user()->podeAtender(); @endphp

<div class="sn-titulo">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('chamados.index') }}" class="btn btn-sm btn-light" title="Voltar"><i class="bi bi-chevron-left"></i></a>
    <div>
        <div class="sn-tipo">Incidente</div>
        <h1>{{ $chamado->numero }}</h1>
    </div>
    <span class="sn-estado text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        @if ($atende)
            @if ($chamado->tecnico_id !== auth()->id() && ! $chamado->finalizado())
                <button type="button" class="btn btn-sm btn-light" id="btn-assumir"><i class="bi bi-person-check"></i> Assumir</button>
            @endif
            <button type="submit" class="btn btn-sm btn-primary" form="form-atendimento">Atualizar</button>
            @unless ($chamado->finalizado())
                <button type="button" class="btn btn-sm btn-success" id="btn-resolver"><i class="bi bi-check2-circle"></i> Resolver</button>
            @endunless
        @endif
        @can('editar-chamado', $chamado)
            <a class="btn btn-sm btn-light" href="{{ route('chamados.edit', $chamado) }}"><i class="bi bi-pencil"></i> Editar</a>
        @endcan
        @can('admin')
            <form action="{{ route('chamados.destroy', $chamado) }}" method="POST" class="m-0">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir" onclick="return confirm('Excluir o incidente {{ $chamado->numero }}?')"><i class="bi bi-trash"></i></button>
            </form>
        @endcan
    </div>
</div>

<div class="card mb-3"><div class="card-body p-3 p-md-4">
    <div class="row">
        <div class="col-lg-6">
            <div class="sn-campo"><span class="rotulo">Número</span><div class="valor fw-semibold">{{ $chamado->numero }}</div></div>
            <div class="sn-campo"><span class="rotulo">Solicitante</span>
                <div class="valor">{{ $chamado->solicitante->name }} @if($chamado->solicitante->setor)<span class="text-secondary">· {{ $chamado->solicitante->setor }}</span>@endif</div>
            </div>
            <div class="sn-campo"><span class="rotulo">Categoria</span><div class="valor">{{ $chamado->categoria->nome }}</div></div>
            <div class="sn-campo"><span class="rotulo">Aberto em</span><div class="valor">{{ $chamado->created_at->format('d/m/Y H:i') }}</div></div>
            <div class="sn-campo"><span class="rotulo">Atualizado</span><div class="valor">{{ $chamado->updated_at->format('d/m/Y H:i') }} <span class="text-secondary small">({{ $chamado->updated_at->diffForHumans() }})</span></div></div>
            @if ($chamado->resolvido_em)
                <div class="sn-campo"><span class="rotulo">Resolvido em</span><div class="valor">{{ $chamado->resolvido_em->format('d/m/Y H:i') }}</div></div>
            @endif
        </div>
        <div class="col-lg-6">
            <div class="sn-campo">
                @if ($atende)
                    <label for="status">Estado</label>
                    <div>
                        <select id="status" name="status" form="form-atendimento" class="form-select form-select-sm @error('status') is-invalid @enderror">
                            @foreach (\App\Models\Chamado::STATUS as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected(old('status', $chamado->status) === $valor)>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @else
                    <span class="rotulo">Estado</span><div class="valor">{{ $chamado->status_label }}</div>
                @endif
            </div>
            <div class="sn-campo"><span class="rotulo">Impacto</span><div class="valor">{{ $chamado->impacto_label }}</div></div>
            <div class="sn-campo"><span class="rotulo">Urgência</span><div class="valor">{{ $chamado->urgencia_label }}</div></div>
            <div class="sn-campo"><span class="rotulo">Prioridade</span>
                <div class="valor fw-semibold"><span class="sn-ponto sn-prio-{{ $chamado->prioridade }}"></span>{{ $chamado->prioridade_label }}</div>
            </div>
            <div class="sn-campo">
                @if ($atende)
                    <label for="grupo_id">Grupo de atribuição</label>
                    <div>
                        <select id="grupo_id" name="grupo_id" form="form-atendimento" class="form-select form-select-sm @error('grupo_id') is-invalid @enderror">
                            <option value="">-- Nenhum --</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}" @selected(old('grupo_id', $chamado->grupo_id) == $grupo->id)>{{ $grupo->nome }}</option>
                            @endforeach
                        </select>
                        @error('grupo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @else
                    <span class="rotulo">Grupo de atribuição</span><div class="valor">{{ $chamado->grupo?->nome ?? '—' }}</div>
                @endif
            </div>
            <div class="sn-campo">
                @if ($atende)
                    <label for="tecnico_id">Atribuído a</label>
                    <div>
                        <select id="tecnico_id" name="tecnico_id" form="form-atendimento" class="form-select form-select-sm @error('tecnico_id') is-invalid @enderror">
                            <option value="">-- Nenhum --</option>
                            @foreach ($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}" data-grupo="{{ $tecnico->grupo_id }}" @selected(old('tecnico_id', $chamado->tecnico_id) == $tecnico->id)>
                                    {{ $tecnico->name }}@if($tecnico->grupo) ({{ $tecnico->grupo->nome }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('tecnico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @else
                    <span class="rotulo">Atribuído a</span><div class="valor">{{ $chamado->tecnico?->name ?? '—' }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="sn-campo sn-campo-largo"><span class="rotulo">Descrição resumida</span><div class="valor fw-semibold">{{ $chamado->titulo }}</div></div>
    <div class="sn-campo sn-campo-largo align-items-start"><span class="rotulo pt-1">Descrição</span>
        <div class="valor border rounded bg-light px-2" style="white-space: pre-line">{{ $chamado->descricao }}</div>
    </div>

    {{-- Notas: técnicos escolhem entre nota de trabalho (interna) e comentário (visível ao solicitante) --}}
    <div class="sn-secao">Notas</div>
    @if ($atende)
        <form id="form-atendimento" action="{{ route('chamados.status', $chamado) }}" method="POST">
            @csrf @method('PATCH')
            <div class="sn-campo sn-campo-largo align-items-start">
                <span class="rotulo pt-1">Tipo</span>
                <div>
                    <div class="btn-group btn-group-sm sn-tipo-tab mb-2" role="group" aria-label="Tipo de anotação">
                        <input type="radio" class="btn-check" name="tipo" id="tipo-nota" value="nota" @checked(old('tipo', 'nota') === 'nota')>
                        <label class="btn btn-light" for="tipo-nota"><i class="bi bi-lock"></i> Nota de trabalho</label>
                        <input type="radio" class="btn-check" name="tipo" id="tipo-comentario" value="comentario" @checked(old('tipo') === 'comentario')>
                        <label class="btn btn-light" for="tipo-comentario"><i class="bi bi-chat-left-text"></i> Comentário adicional (visível ao solicitante)</label>
                    </div>
                    <textarea id="comentario" name="comentario" rows="3" class="form-control form-control-sm @error('comentario') is-invalid @enderror" maxlength="2000"
                              placeholder="Notas de trabalho são internas da equipe de TI.">{{ old('comentario') }}</textarea>
                    @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-sm btn-primary" type="submit">Publicar e atualizar</button>
                    </div>
                </div>
            </div>
        </form>
    @elseif (! in_array($chamado->status, ['fechado', 'cancelado']))
        <form action="{{ route('chamados.comentar', $chamado) }}" method="POST">
            @csrf
            <div class="sn-campo sn-campo-largo align-items-start">
                <label for="comentario" class="pt-1">Comentários adicionais</label>
                <div>
                    <textarea id="comentario" name="comentario" rows="3" class="form-control form-control-sm @error('comentario') is-invalid @enderror" maxlength="2000"
                              placeholder="Envie uma informação ou resposta para a equipe de TI.">{{ old('comentario') }}</textarea>
                    @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-sm btn-primary" type="submit">Publicar</button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <p class="text-secondary small mb-0">Este incidente está encerrado. Para um novo problema, registre outro incidente.</p>
    @endif
</div></div>

<div class="card">
    <div class="card-header d-flex align-items-center py-2">
        <span class="fw-semibold">Atividades</span>
        <span class="badge text-bg-secondary ms-2">{{ $atividades->count() }}</span>
        @if ($atende)
            <span class="ms-auto small text-secondary">
                <span class="sn-ponto" style="background: var(--sn-nota)"></span>Nota de trabalho
                <span class="sn-ponto ms-3" style="background: var(--sn-comentario)"></span>Comentário
            </span>
        @endif
    </div>
    <div class="card-body">
        @foreach ($atividades as $atividade)
            @php $nome = $atividade->user?->name ?? 'Sistema'; @endphp
            <div class="sn-atividade {{ $atividade->isNota() ? 'nota' : '' }}">
                <div class="cab">
                    <span class="sn-avatar" style="width:26px;height:26px;font-size:.7rem">{{ collect(explode(' ', $nome))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}</span>
                    <strong>{{ $nome }}</strong>
                    <span>· {{ \App\Models\ChamadoHistorico::TIPOS[$atividade->tipo] ?? $atividade->tipo }}</span>
                    <span class="ms-auto" title="{{ $atividade->created_at->format('d/m/Y H:i:s') }}">{{ $atividade->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if ($atividade->status_anterior && $atividade->status_anterior !== $atividade->status_novo)
                    <div class="mudanca">
                        Estado: <strong>{{ \App\Models\Chamado::STATUS[$atividade->status_novo] ?? $atividade->status_novo }}</strong>
                        <span class="text-secondary">era {{ \App\Models\Chamado::STATUS[$atividade->status_anterior] ?? $atividade->status_anterior }}</span>
                    </div>
                @endif
                @if ($atividade->comentario)
                    <div class="texto">{{ $atividade->comentario }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection

@if ($atende)
@push('scripts')
<script>
    (function () {
        const form = document.getElementById('form-atendimento');
        const status = document.getElementById('status');
        const comentario = document.getElementById('comentario');

        // Assumir: atribui a mim, coloca no meu grupo e inicia o atendimento.
        document.getElementById('btn-assumir')?.addEventListener('click', function () {
            const tecnico = document.getElementById('tecnico_id');
            tecnico.value = '{{ auth()->id() }}';
            const grupoDoTecnico = tecnico.selectedOptions[0]?.dataset.grupo;
            if (grupoDoTecnico) document.getElementById('grupo_id').value = grupoDoTecnico;
            if (status.value === 'aberto') status.value = 'em_andamento';
            form.requestSubmit();
        });

        // Resolver: exige notas de resolução, que ficam visíveis ao solicitante.
        document.getElementById('btn-resolver')?.addEventListener('click', function () {
            status.value = 'resolvido';
            document.getElementById('tipo-comentario').checked = true;
            if (!comentario.value.trim()) {
                comentario.classList.add('is-invalid');
                comentario.placeholder = 'Descreva a solução aplicada (obrigatório para resolver).';
                comentario.focus();
                return;
            }
            form.requestSubmit();
        });
    })();
</script>
@endpush
@endif
