@extends('layouts.app')
@section('title', 'Grupos de atribuição')
@section('content')
<div class="sn-titulo">
    <div><div class="sn-tipo">Administração</div><h1>Grupos de atribuição</h1></div>
    <span class="text-secondary small d-none d-md-inline">Equipes que recebem e tratam os incidentes</span>
    <a class="btn btn-sm btn-primary ms-auto" href="{{ route('grupos.create') }}"><i class="bi bi-plus-lg"></i> Novo grupo</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 sn-lista">
            <thead class="table-light"><tr><th>Nome</th><th>Descrição</th><th>Membros</th><th>Incidentes</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($grupos as $grupo)
                <tr>
                    <td class="fw-semibold">{{ $grupo->nome }}</td>
                    <td class="text-secondary">{{ \Illuminate\Support\Str::limit($grupo->descricao, 80) ?: '—' }}</td>
                    <td>{{ $grupo->membros_count }}</td>
                    <td><a href="{{ route('chamados.index', ['grupo_id' => $grupo->id]) }}">{{ $grupo->chamados_count }}</a></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('grupos.edit', $grupo) }}">Editar</a>
                        <form class="d-inline" action="{{ route('grupos.destroy', $grupo) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Excluir este grupo? Os incidentes e membros ficarão sem grupo.')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-5">Nenhum grupo cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($grupos->hasPages())
        <div class="card-footer bg-white">{{ $grupos->links() }}</div>
    @endif
</div>
@endsection
