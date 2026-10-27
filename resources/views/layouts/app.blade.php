<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Chamados de TI') · HelpDesk TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        .stat-card .display-6 { font-weight: 600; }
        .timeline { border-left: 2px solid var(--bs-border-color); padding-left: 1rem; }
        .timeline-item { position: relative; }
        .timeline-item::before { content: ''; position: absolute; left: -1.42rem; top: .35rem; width: .7rem; height: .7rem; border-radius: 50%; background: var(--bs-primary); }
    </style>
</head>
<body class="bg-light">
@auth
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}"><i class="bi bi-headset"></i> HelpDesk TI</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">Painel</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('chamados.*')) active @endif" href="{{ route('chamados.index') }}">Chamados</a></li>
                @can('admin')
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('categorias.*')) active @endif" href="{{ route('categorias.index') }}">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('usuarios.*')) active @endif" href="{{ route('usuarios.index') }}">Usuários</a></li>
                @endcan
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="navbar-text text-white small">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                    <span class="badge text-bg-light">{{ auth()->user()->perfil_label }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit"><i class="bi bi-box-arrow-right"></i> Sair</button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endauth
<main class="container py-4">
    @if (session('sucesso'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('sucesso') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @if (session('erro'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('erro') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
