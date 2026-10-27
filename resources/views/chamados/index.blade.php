@extends('layouts.app')
@section('title', 'Chamados')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Chamados</h1>
        <p class="text-secondary mb-0">{{ $chamados->total() }} chamado(s) encontrado(s)</p>
    </div>
    <a class="btn btn-primary" href="{{ route('chamados.create') }}"><i class="bi bi-plus-lg"></i> Abrir chamado</a>
</div>

<form method="GET" class="card border-0 shadow-sm mb-4"><div class="card-body">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1" for="busca">Buscar</label>
            <input id="busca" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Título, descrição ou #número">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">Todos</option>
                @foreach (\App\Models\Chamado::STATUS as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="prioridade">Prioridade</label>
            <select id="prioridade" name="prioridade" class="form-select">
                <option value="">Todas</option>
                @foreach (\App\Models\Chamado::PRIORIDADES as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(request('prioridade') === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="categoria_id">Categoria</label>
            <select id="categoria_id" name="categoria_id" class="form-select">
                <option value="">Todas</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                @endforeach
            </select>
        </div>
        @can('atender-chamados')
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="tecnico_id">Técnico</label>
            <select id="tecnico_id" name="tecnico_id" class="form-select">
                <option value="">Todos</option>
                <option value="nenhum" @selected(request('tecnico_id') === 'nenhum')>Sem técnico</option>
                @foreach ($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}" @selected(request('tecnico_id') == $tecnico->id)>{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
        @endcan
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-primary w-100" type="submit" title="Filtrar"><i class="bi bi-funnel"></i></button>
            <a class="btn btn-outline-secondary" href="{{ route('chamados.index') }}" title="Limpar"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</div></form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Título</th><th>Categoria</th><th>Prioridade</th><th>Status</th><th>Solicitante</th><th>Técnico</th><th>Aberto em</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
            @forelse ($chamados as $chamado)
                <tr>
                    <td class="text-secondary">{{ $chamado->id }}</td>
                    <td class="fw-semibold"><a class="link-dark" href="{{ route('chamados.show', $chamado) }}">{{ \Illuminate\Support\Str::limit($chamado->titulo, 50) }}</a></td>
                    <td>{{ $chamado->categoria->nome }}</td>
                    <td><span class="badge text-bg-{{ $chamado->prioridade_cor }}">{{ $chamado->prioridade_label }}</span></td>
                    <td><span class="badge text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span></td>
                    <td>{{ $chamado->solicitante->name }}</td>
                    <td>{{ $chamado->tecnico?->name ?? '—' }}</td>
                    <td class="text-nowrap">{{ $chamado->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('chamados.show', $chamado) }}" title="Ver"><i class="bi bi-eye"></i></a>
                        @can('editar-chamado', $chamado)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('chamados.edit', $chamado) }}" title="Editar"><i class="bi bi-pencil"></i></a>
                        @endcan
                        @can('admin')
                            <form class="d-inline" action="{{ route('chamados.destroy', $chamado) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir" onclick="return confirm('Excluir o chamado #{{ $chamado->id }}?')"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-secondary py-5">Nenhum chamado encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($chamados->hasPages())
        <div class="card-footer bg-white">{{ $chamados->links() }}</div>
    @endif
</div>
@endsection
