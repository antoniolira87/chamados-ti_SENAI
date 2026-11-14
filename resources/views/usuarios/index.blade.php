@extends('layouts.app')
@section('title', 'Usuários')
@section('content')
<div class="sn-titulo">
    <div><div class="sn-tipo">Administração</div><h1>Usuários</h1></div>
    <span class="text-secondary small d-none d-md-inline">Solicitantes, técnicos e administradores</span>
    <a class="btn btn-sm btn-primary ms-auto" href="{{ route('usuarios.create') }}"><i class="bi bi-plus-lg"></i> Novo usuário</a>
</div>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome ou e-mail"></div>
    <div class="col-md-3">
        <select name="perfil" class="form-select">
            <option value="">Todos os perfis</option>
            @foreach (\App\Models\User::PERFIS as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(request('perfil') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 sn-lista">
            <thead class="table-light"><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Setor</th><th>Grupo</th><th>Abertos</th><th>Atribuídos</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($usuarios as $usuario)
                <tr>
                    <td class="fw-semibold"><a class="link-dark" href="{{ route('usuarios.show', $usuario) }}">{{ $usuario->name }}</a></td>
                    <td>{{ $usuario->email }}</td>
                    <td><span class="badge text-bg-{{ ['admin' => 'danger', 'tecnico' => 'primary'][$usuario->perfil] ?? 'secondary' }}">{{ $usuario->perfil_label }}</span></td>
                    <td>{{ $usuario->setor ?: '—' }}</td>
                    <td>{{ $usuario->grupo?->nome ?? '—' }}</td>
                    <td>{{ $usuario->chamados_abertos_count }}</td>
                    <td>{{ $usuario->chamados_atribuidos_count }}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
                        @unless ($usuario->is(auth()->user()))
                            <form class="d-inline" action="{{ route('usuarios.destroy', $usuario) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Excluir este usuário? Os chamados abertos por ele também serão removidos.')">Excluir</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-5">Nenhum usuário encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($usuarios->hasPages())
        <div class="card-footer bg-white">{{ $usuarios->links() }}</div>
    @endif
</div>
@endsection
