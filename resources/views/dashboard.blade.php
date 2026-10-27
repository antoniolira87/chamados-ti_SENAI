@extends('layouts.app')
@section('title', 'Painel')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Olá, {{ auth()->user()->name }}</h1>
        <p class="text-secondary mb-0">
            @if (auth()->user()->podeAtender()) Visão geral de todos os chamados. @else Resumo dos seus chamados. @endif
        </p>
    </div>
    <a class="btn btn-primary" href="{{ route('chamados.create') }}"><i class="bi bi-plus-lg"></i> Abrir chamado</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm stat-card h-100"><div class="card-body">
            <div class="text-secondary small">Total</div>
            <div class="display-6">{{ $total }}</div>
        </div></div>
    </div>
    @foreach (\App\Models\Chamado::STATUS as $chave => $rotulo)
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('chamados.index', ['status' => $chave]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm stat-card h-100 border-start border-4 border-{{ \App\Models\Chamado::STATUS_CORES[$chave] }}"><div class="card-body">
                    <div class="text-secondary small">{{ $rotulo }}</div>
                    <div class="display-6 text-body">{{ $porStatus[$chave] ?? 0 }}</div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Chamados recentes</span>
                <a href="{{ route('chamados.index') }}" class="small">Ver todos</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($recentes as $chamado)
                    <a href="{{ route('chamados.show', $chamado) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold text-truncate">#{{ $chamado->id }} · {{ $chamado->titulo }}</span>
                            <span class="badge text-bg-{{ $chamado->status_cor }} align-self-start">{{ $chamado->status_label }}</span>
                        </div>
                        <small class="text-secondary">
                            {{ $chamado->categoria->nome }} · {{ $chamado->created_at->diffForHumans() }}
                            · {{ $chamado->tecnico?->name ?? 'Sem técnico' }}
                        </small>
                    </a>
                @empty
                    <div class="list-group-item text-secondary text-center py-4">Nenhum chamado ainda.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Pendentes por prioridade</div>
            <ul class="list-group list-group-flush">
                @foreach (\App\Models\Chamado::PRIORIDADES as $chave => $rotulo)
                    <li class="list-group-item d-flex justify-content-between">
                        <span><span class="badge text-bg-{{ \App\Models\Chamado::PRIORIDADE_CORES[$chave] }}">{{ $rotulo }}</span></span>
                        <span class="fw-semibold">{{ $porPrioridade[$chave] ?? 0 }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @if (auth()->user()->podeAtender())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Meus atendimentos</div>
                <div class="list-group list-group-flush">
                    @forelse ($meusAtendimentos as $chamado)
                        <a href="{{ route('chamados.show', $chamado) }}" class="list-group-item list-group-item-action small">
                            <span class="badge text-bg-{{ $chamado->prioridade_cor }}">{{ $chamado->prioridade_label }}</span>
                            #{{ $chamado->id }} {{ \Illuminate\Support\Str::limit($chamado->titulo, 40) }}
                        </a>
                    @empty
                        <div class="list-group-item text-secondary small">Nenhum chamado atribuído a você.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
