@extends('layouts.app')
@section('title', 'Painel')
@section('content')
@php
    $atende = auth()->user()->podeAtender();
    $maxPrioridade = max(1, $porPrioridade->max() ?? 0);
    $maxStatus = max(1, $porStatus->max() ?? 0);
    $coresStatus = ['primary' => '#1f73b7', 'warning' => '#f28a1a', 'info' => '#3aa6c9', 'success' => '#2e9e5b', 'secondary' => '#8a98a3', 'dark' => '#38444d'];
@endphp

<div class="sn-titulo">
    <div>
        <div class="sn-tipo">{{ $atende ? 'Visão do atendimento' : 'Autoatendimento' }}</div>
        <h1>Olá, {{ auth()->user()->name }}</h1>
    </div>
    <a class="btn btn-sm btn-primary ms-auto" href="{{ route('chamados.create') }}"><i class="bi bi-plus-lg"></i> Registrar incidente</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <a class="sn-kpi" href="{{ route('chamados.index', ['fila' => 'pendentes']) }}">
            <div class="rotulo">Incidentes abertos</div>
            <div class="numero">{{ $indicadores['pendentes'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a class="sn-kpi" style="border-top-color:#d6292f" href="{{ route('chamados.index', ['fila' => 'pendentes', 'ordem' => 'prioridade', 'direcao' => 'asc']) }}">
            <div class="rotulo">Críticos e altos (P1/P2)</div>
            <div class="numero">{{ $indicadores['criticos'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a class="sn-kpi" style="border-top-color:#f28a1a" href="{{ route('chamados.index', ['fila' => 'sem_atribuicao']) }}">
            <div class="rotulo">Sem atribuição</div>
            <div class="numero">{{ $indicadores['sem_atribuicao'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        @if ($atende)
            <a class="sn-kpi" style="border-top-color:#62d84e" href="{{ route('chamados.index', ['fila' => 'meus']) }}">
                <div class="rotulo">Atribuídos a mim</div>
                <div class="numero">{{ $indicadores['meus'] }}</div>
            </a>
        @else
            <a class="sn-kpi" style="border-top-color:#62d84e" href="{{ route('chamados.index', ['status' => 'resolvido']) }}">
                <div class="rotulo">Resolvidos (aguardando fechamento)</div>
                <div class="numero">{{ $indicadores['meus'] }}</div>
            </a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold">Incidentes recentes</span>
                <a href="{{ route('chamados.index') }}" class="small">Ver lista</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 sn-lista">
                    <thead><tr><th class="ps-3">Número</th><th>Descrição resumida</th><th>Prioridade</th><th>Estado</th><th>Atribuído a</th></tr></thead>
                    <tbody>
                    @forelse ($recentes as $chamado)
                        <tr>
                            <td class="ps-3"><a class="sn-numero" href="{{ route('chamados.show', $chamado) }}">{{ $chamado->numero }}</a></td>
                            <td>{{ \Illuminate\Support\Str::limit($chamado->titulo, 45) }}<div class="small text-secondary">{{ $chamado->categoria->nome }} · {{ $chamado->created_at->diffForHumans() }}</div></td>
                            <td class="text-nowrap"><span class="sn-ponto sn-prio-{{ $chamado->prioridade }}"></span>{{ $chamado->prioridade_label }}</td>
                            <td><span class="sn-estado text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span></td>
                            <td class="text-nowrap">{{ $chamado->tecnico?->name ?? '(vazio)' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Nenhum incidente ainda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-flex flex-column gap-3">
        <div class="card">
            <div class="card-header fw-semibold py-2">Abertos por prioridade</div>
            <div class="card-body">
                @foreach (\App\Models\Chamado::PRIORIDADES as $chave => $rotulo)
                    @php $qtd = $porPrioridade[$chave] ?? 0; @endphp
                    <a href="{{ route('chamados.index', ['fila' => 'pendentes', 'prioridade' => $chave]) }}" class="d-block text-decoration-none text-body mb-2">
                        <div class="d-flex justify-content-between small"><span><span class="sn-ponto sn-prio-{{ $chave }}"></span>{{ $rotulo }}</span><strong>{{ $qtd }}</strong></div>
                        <div class="sn-barra"><span class="sn-prio-{{ $chave }}" style="width: {{ $qtd / $maxPrioridade * 100 }}%"></span></div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold py-2 d-flex justify-content-between">Por estado <span class="text-secondary fw-normal small">{{ $total }} no total</span></div>
            <div class="card-body">
                @foreach (\App\Models\Chamado::STATUS as $chave => $rotulo)
                    @php $qtd = $porStatus[$chave] ?? 0; @endphp
                    <a href="{{ route('chamados.index', ['status' => $chave]) }}" class="d-block text-decoration-none text-body mb-2">
                        <div class="d-flex justify-content-between small"><span>{{ $rotulo }}</span><strong>{{ $qtd }}</strong></div>
                        <div class="sn-barra"><span style="width: {{ $qtd / $maxStatus * 100 }}%; background: {{ $coresStatus[\App\Models\Chamado::STATUS_CORES[$chave]] }}"></span></div>
                    </a>
                @endforeach
            </div>
        </div>

        @if ($atende)
            <div class="card">
                <div class="card-header fw-semibold py-2">Minha fila</div>
                <div class="list-group list-group-flush">
                    @forelse ($meusAtendimentos as $chamado)
                        <a href="{{ route('chamados.show', $chamado) }}" class="list-group-item list-group-item-action small">
                            <span class="sn-ponto sn-prio-{{ $chamado->prioridade }}"></span>
                            <strong>{{ $chamado->numero }}</strong> {{ \Illuminate\Support\Str::limit($chamado->titulo, 32) }}
                        </a>
                    @empty
                        <div class="list-group-item text-secondary small">Nenhum incidente atribuído a você.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
