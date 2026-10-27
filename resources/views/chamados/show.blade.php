@extends('layouts.app')
@section('title', "Chamado #{$chamado->id}")
@section('content')
<div class="mb-3"><a href="{{ route('chamados.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Voltar para chamados</a></div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <p class="text-secondary mb-1">Chamado #{{ $chamado->id }}</p>
                    <h1 class="h3 mb-2">{{ $chamado->titulo }}</h1>
                    <span class="badge text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span>
                    <span class="badge text-bg-{{ $chamado->prioridade_cor }}">Prioridade {{ $chamado->prioridade_label }}</span>
                    <span class="badge text-bg-light border">{{ $chamado->categoria->nome }}</span>
                </div>
                <div class="d-flex gap-2">
                    @can('editar-chamado', $chamado)
                        <a class="btn btn-outline-primary" href="{{ route('chamados.edit', $chamado) }}"><i class="bi bi-pencil"></i> Editar</a>
                    @endcan
                    @can('admin')
                        <form action="{{ route('chamados.destroy', $chamado) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger" type="submit" onclick="return confirm('Excluir este chamado?')"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </div>
            </div>
            <div class="bg-light rounded p-3" style="white-space: pre-line">{{ $chamado->descricao }}</div>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="h5 mb-3">Histórico</h2>
            <div class="timeline">
                @foreach ($chamado->historicos as $historico)
                    <div class="timeline-item mb-3">
                        <div class="small text-secondary">
                            {{ $historico->created_at->format('d/m/Y H:i') }} · {{ $historico->user?->name ?? 'Sistema' }}
                        </div>
                        <div>
                            @if ($historico->status_anterior && $historico->status_anterior !== $historico->status_novo)
                                Status alterado de <strong>{{ \App\Models\Chamado::STATUS[$historico->status_anterior] ?? $historico->status_anterior }}</strong>
                                para <strong>{{ \App\Models\Chamado::STATUS[$historico->status_novo] ?? $historico->status_novo }}</strong>.
                            @endif
                            {{ $historico->comentario }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4"><div class="card-body">
            <dl class="mb-0">
                <dt class="small text-secondary">Solicitante</dt>
                <dd>{{ $chamado->solicitante->name }} @if($chamado->solicitante->setor)<span class="text-secondary">· {{ $chamado->solicitante->setor }}</span>@endif</dd>
                <dt class="small text-secondary">Técnico responsável</dt>
                <dd>{{ $chamado->tecnico?->name ?? 'Não atribuído' }}</dd>
                <dt class="small text-secondary">Aberto em</dt>
                <dd>{{ $chamado->created_at->format('d/m/Y H:i') }}</dd>
                <dt class="small text-secondary">Última atualização</dt>
                <dd>{{ $chamado->updated_at->format('d/m/Y H:i') }}</dd>
                @if ($chamado->resolvido_em)
                    <dt class="small text-secondary">Resolvido em</dt>
                    <dd class="mb-0">{{ $chamado->resolvido_em->format('d/m/Y H:i') }}</dd>
                @endif
            </dl>
        </div></div>

        @can('atender-chamados')
        <div class="card border-0 shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Atendimento</h2>
            <form action="{{ route('chamados.status', $chamado) }}" method="POST">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label for="status" class="form-label small">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach (\App\Models\Chamado::STATUS as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected(old('status', $chamado->status) === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="tecnico_id" class="form-label small">Técnico responsável</label>
                    <select id="tecnico_id" name="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror">
                        <option value="">— Não atribuído —</option>
                        @foreach ($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" @selected(old('tecnico_id', $chamado->tecnico_id) == $tecnico->id)>{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                    @error('tecnico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="comentario" class="form-label small">Comentário</label>
                    <textarea id="comentario" name="comentario" rows="3" class="form-control @error('comentario') is-invalid @enderror" maxlength="2000" placeholder="Opcional">{{ old('comentario') }}</textarea>
                    @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Atualizar</button>
                    @if (! $chamado->tecnico_id || $chamado->tecnico_id !== auth()->id())
                        <button class="btn btn-outline-primary" type="submit" name="tecnico_id" value="{{ auth()->id() }}" title="Assumir chamado"
                                onclick="document.getElementById('tecnico_id').disabled = true; if (document.getElementById('status').value === 'aberto') document.getElementById('status').value = 'em_andamento';">
                            <i class="bi bi-person-check"></i> Assumir
                        </button>
                    @endif
                </div>
            </form>
        </div></div>
        @endcan
    </div>
</div>
@endsection
