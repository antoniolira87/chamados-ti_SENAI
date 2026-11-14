@extends('layouts.app')
@section('title', 'Incidentes')
@section('content')
@php
    $filas = [
        'meus' => 'Atribuídos a mim',
        'grupo' => 'Do meu grupo',
        'sem_atribuicao' => 'Sem atribuição',
        'pendentes' => 'Abertos',
    ];
    // Trilha de filtros ativos, como a "breadcrumb" de condições das listas ITSM.
    $trilha = array_filter([
        request('fila') ? 'Fila = '.($filas[request('fila')] ?? request('fila')) : null,
        request('busca') ? 'Contém "'.request('busca').'"' : null,
        request('status') ? 'Estado = '.(\App\Models\Chamado::STATUS[request('status')] ?? request('status')) : null,
        request('prioridade') ? 'Prioridade = '.(\App\Models\Chamado::PRIORIDADES[request('prioridade')] ?? request('prioridade')) : null,
        request('categoria_id') ? 'Categoria = '.($categorias->firstWhere('id', request('categoria_id'))?->nome ?? '?') : null,
        request('grupo_id') ? 'Grupo = '.($grupos->firstWhere('id', request('grupo_id'))?->nome ?? '?') : null,
        request('tecnico_id') ? 'Atribuído a = '.(request('tecnico_id') === 'nenhum' ? '(vazio)' : ($tecnicos->firstWhere('id', request('tecnico_id'))?->name ?? '?')) : null,
    ]);
    $ordem = request('ordem', 'created_at');
    $direcao = request('direcao', 'desc');
    $ordenar = function (string $coluna, string $rotulo) use ($ordem, $direcao) {
        $nova = $ordem === $coluna && $direcao === 'desc' ? 'asc' : 'desc';
        $icone = $ordem === $coluna ? ($direcao === 'asc' ? ' <i class="bi bi-caret-up-fill small"></i>' : ' <i class="bi bi-caret-down-fill small"></i>') : '';
        $url = request()->fullUrlWithQuery(['ordem' => $coluna, 'direcao' => $nova, 'page' => null]);

        return '<a href="'.e($url).'">'.e($rotulo).$icone.'</a>';
    };
@endphp

<div class="sn-titulo">
    <div>
        <div class="sn-tipo">Incidentes</div>
        <h1>{{ $filas[request('fila')] ?? (auth()->user()->podeAtender() ? 'Todos os incidentes' : 'Meus incidentes') }}</h1>
    </div>
    <form method="GET" class="d-flex gap-2 ms-md-4 flex-grow-1" style="max-width: 420px" role="search">
        @foreach (request()->except(['busca', 'page']) as $chave => $valor)
            <input type="hidden" name="{{ $chave }}" value="{{ $valor }}">
        @endforeach
        <input name="busca" value="{{ request('busca') }}" class="form-control form-control-sm" placeholder="Pesquisar número, descrição...">
        <button class="btn btn-sm btn-light" type="submit" title="Pesquisar"><i class="bi bi-search"></i></button>
    </form>
    <div class="ms-auto d-flex gap-2">
        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtros" aria-expanded="false" aria-controls="filtros">
            <i class="bi bi-funnel"></i> Filtros
        </button>
        <a class="btn btn-sm btn-primary" href="{{ route('chamados.create') }}"><i class="bi bi-plus-lg"></i> Novo</a>
    </div>
</div>

<div class="collapse @if(request()->hasAny(['status', 'prioridade', 'categoria_id', 'grupo_id', 'tecnico_id'])) show @endif" id="filtros">
    <form method="GET" class="card mb-3"><div class="card-body py-3">
        @if (request('fila')) <input type="hidden" name="fila" value="{{ request('fila') }}"> @endif
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="status">Estado</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Chamado::STATUS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="prioridade">Prioridade</label>
                <select id="prioridade" name="prioridade" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Chamado::PRIORIDADES as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(request('prioridade') == $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                    @endforeach
                </select>
            </div>
            @can('atender-chamados')
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="grupo_id">Grupo de atribuição</label>
                <select id="grupo_id" name="grupo_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" @selected(request('grupo_id') == $grupo->id)>{{ $grupo->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="tecnico_id">Atribuído a</label>
                <select id="tecnico_id" name="tecnico_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="nenhum" @selected(request('tecnico_id') === 'nenhum')>(vazio)</option>
                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}" @selected(request('tecnico_id') == $tecnico->id)>{{ $tecnico->name }}</option>
                    @endforeach
                </select>
            </div>
            @endcan
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary flex-fill" type="submit">Aplicar</button>
                <a class="btn btn-sm btn-light" href="{{ route('chamados.index') }}" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </div>
    </div></form>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2 py-2 sn-trilha">
        <i class="bi bi-funnel-fill"></i>
        <a href="{{ route('chamados.index') }}">Todos</a>
        @foreach ($trilha as $condicao)
            <i class="bi bi-chevron-right small"></i> <span class="filtro">{{ $condicao }}</span>
        @endforeach
        <span class="ms-auto">{{ $chamados->total() }} registro(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 sn-lista">
            <thead>
                <tr>
                    <th class="ps-3">{!! $ordenar('numero', 'Número') !!}</th>
                    <th>{!! $ordenar('created_at', 'Aberto em') !!}</th>
                    <th>{!! $ordenar('titulo', 'Descrição resumida') !!}</th>
                    <th>Solicitante</th>
                    <th>{!! $ordenar('prioridade', 'Prioridade') !!}</th>
                    <th>{!! $ordenar('status', 'Estado') !!}</th>
                    <th>Categoria</th>
                    <th>Grupo de atribuição</th>
                    <th>Atribuído a</th>
                    <th>{!! $ordenar('updated_at', 'Atualizado') !!}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($chamados as $chamado)
                <tr>
                    <td class="ps-3"><a class="sn-numero" href="{{ route('chamados.show', $chamado) }}">{{ $chamado->numero }}</a></td>
                    <td class="text-nowrap">{{ $chamado->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($chamado->titulo, 55) }}</td>
                    <td class="text-nowrap">{{ $chamado->solicitante->name }}</td>
                    <td class="text-nowrap"><span class="sn-ponto sn-prio-{{ $chamado->prioridade }}"></span>{{ $chamado->prioridade_label }}</td>
                    <td><span class="sn-estado text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span></td>
                    <td class="text-nowrap">{{ $chamado->categoria->nome }}</td>
                    <td class="text-nowrap">{{ $chamado->grupo?->nome ?? '(vazio)' }}</td>
                    <td class="text-nowrap">{{ $chamado->tecnico?->name ?? '(vazio)' }}</td>
                    <td class="text-nowrap text-secondary small">{{ $chamado->updated_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-secondary py-5">Nenhum registro encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($chamados->hasPages())
        <div class="card-footer bg-white">{{ $chamados->links() }}</div>
    @endif
</div>
@endsection
