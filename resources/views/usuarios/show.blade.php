@extends('layouts.app')
@section('title', $usuario->name)
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div><p class="text-secondary mb-1">{{ $usuario->perfil_label }}</p><h1 class="h3 mb-0">{{ $usuario->name }}</h1></div>
            <a class="btn btn-outline-primary" href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
        </div>
        <dl class="row mb-0">
            <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $usuario->email }}</dd>
            <dt class="col-sm-4">Setor</dt><dd class="col-sm-8">{{ $usuario->setor ?: '—' }}</dd>
            <dt class="col-sm-4">Chamados abertos</dt><dd class="col-sm-8">{{ $usuario->chamados_abertos_count }}</dd>
            <dt class="col-sm-4">Chamados atribuídos</dt><dd class="col-sm-8">{{ $usuario->chamados_atribuidos_count }}</dd>
            <dt class="col-sm-4">Cadastrado em</dt><dd class="col-sm-8 mb-0">{{ $usuario->created_at->format('d/m/Y') }}</dd>
        </dl>
    </div></div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">{{ $usuario->podeAtender() ? 'Últimos chamados atribuídos' : 'Últimos chamados abertos' }}</div>
        <div class="list-group list-group-flush">
            @forelse ($chamados as $chamado)
                <a href="{{ route('chamados.show', $chamado) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                    <span>#{{ $chamado->id }} · {{ $chamado->titulo }} <small class="text-secondary">({{ $chamado->categoria->nome }})</small></span>
                    <span class="badge text-bg-{{ $chamado->status_cor }}">{{ $chamado->status_label }}</span>
                </a>
            @empty
                <div class="list-group-item text-secondary">Nenhum chamado.</div>
            @endforelse
        </div>
    </div>
    <a href="{{ route('usuarios.index') }}" class="btn btn-link px-0 mt-3">Voltar</a>
</div></div>
@endsection
