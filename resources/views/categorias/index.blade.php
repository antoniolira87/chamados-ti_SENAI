@extends('layouts.app')
@section('title', 'Categorias')
@section('content')
<div class="sn-titulo">
    <div><div class="sn-tipo">Administração</div><h1>Categorias</h1></div>
    <span class="text-secondary small d-none d-md-inline">Tipos de atendimento de TI</span>
    <a class="btn btn-sm btn-primary ms-auto" href="{{ route('categorias.create') }}"><i class="bi bi-plus-lg"></i> Nova categoria</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 sn-lista">
            <thead class="table-light"><tr><th>Nome</th><th>Descrição</th><th>Chamados</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($categorias as $categoria)
                <tr>
                    <td class="fw-semibold">{{ $categoria->nome }}</td>
                    <td class="text-secondary">{{ \Illuminate\Support\Str::limit($categoria->descricao, 80) ?: '—' }}</td>
                    <td><a href="{{ route('chamados.index', ['categoria_id' => $categoria->id]) }}">{{ $categoria->chamados_count }}</a></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('categorias.edit', $categoria) }}">Editar</a>
                        <form class="d-inline" action="{{ route('categorias.destroy', $categoria) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Excluir esta categoria?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary py-5">Nenhuma categoria cadastrada.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($categorias->hasPages())
        <div class="card-footer bg-white">{{ $categorias->links() }}</div>
    @endif
</div>
@endsection
