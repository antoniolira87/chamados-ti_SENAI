<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Incidentes') · HelpDesk TI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        :root {
            --sn-header: #0b2a3a;
            --sn-nav: #15232d;
            --sn-nav-hover: #1f313d;
            --sn-nav-text: #c3cdd4;
            --sn-nav-muted: #7f8e98;
            --sn-accent: #62d84e;
            --sn-primary: #1f73b7;
            --sn-primary-hover: #185c93;
            --sn-bg: #eef1f4;
            --sn-border: #d3d9de;
            --sn-text: #1b2730;
            --sn-muted: #5f6e78;
            --sn-label: #45545e;
            --sn-nota: #f2c94c;
            --sn-comentario: #8fa0ad;
            --bs-body-font-family: 'Source Sans 3', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --bs-body-font-size: .9375rem;
            --bs-body-color: var(--sn-text);
            --bs-border-color: var(--sn-border);
            --bs-link-color-rgb: 31, 115, 183;
            --bs-link-hover-color-rgb: 24, 92, 147;
            --bs-primary: #1f73b7;
            --bs-primary-rgb: 31, 115, 183;
            --bs-border-radius: .25rem;
        }
        body { background: var(--sn-bg); min-height: 100vh; }
        .btn-primary {
            --bs-btn-bg: var(--sn-primary); --bs-btn-border-color: var(--sn-primary);
            --bs-btn-hover-bg: var(--sn-primary-hover); --bs-btn-hover-border-color: var(--sn-primary-hover);
            --bs-btn-active-bg: var(--sn-primary-hover); --bs-btn-active-border-color: var(--sn-primary-hover);
        }
        .btn-outline-primary {
            --bs-btn-color: var(--sn-primary); --bs-btn-border-color: var(--sn-primary);
            --bs-btn-hover-bg: var(--sn-primary); --bs-btn-hover-border-color: var(--sn-primary);
            --bs-btn-active-bg: var(--sn-primary-hover);
        }
        .btn-light { --bs-btn-border-color: var(--sn-border); --bs-btn-bg: #fff; }

        /* Cabeçalho */
        .sn-header { background: var(--sn-header); color: #fff; height: 52px; position: sticky; top: 0; z-index: 1030; }
        .sn-brand { color: #fff; font-weight: 700; font-size: 1.1rem; letter-spacing: .01em; text-decoration: none; white-space: nowrap; }
        .sn-brand .marca { display: inline-grid; place-items: center; width: 30px; height: 30px; border-radius: 6px; background: var(--sn-accent); color: var(--sn-header); margin-right: .5rem; }
        .sn-busca { max-width: 360px; }
        .sn-busca .form-control { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.18); color: #fff; }
        .sn-busca .form-control::placeholder { color: rgba(255,255,255,.6); }
        .sn-busca .form-control:focus { background: #fff; color: var(--sn-text); }
        .sn-avatar { width: 32px; height: 32px; border-radius: 50%; display: inline-grid; place-items: center; font-weight: 700; font-size: .8rem; background: #3b6e8f; color: #fff; flex-shrink: 0; }
        .sn-header .btn-link { color: #fff; text-decoration: none; }

        /* Navegador lateral */
        .sn-shell { display: flex; min-height: calc(100vh - 52px); }
        /* !important: o .offcanvas-lg do Bootstrap força fundo transparente em telas largas */
        .sn-nav { background: var(--sn-nav) !important; color: var(--sn-nav-text); width: 250px; flex-shrink: 0; }
        @media (min-width: 992px) {
            .sn-nav { position: sticky; top: 52px; height: calc(100vh - 52px); overflow-y: auto; }
        }
        .sn-nav .offcanvas-header { border-bottom: 1px solid rgba(255,255,255,.08); }
        .sn-nav-filtro { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); color: #fff; font-size: .875rem; }
        .sn-nav-filtro::placeholder { color: var(--sn-nav-muted); }
        .sn-nav-filtro:focus { background: rgba(255,255,255,.12); color: #fff; border-color: var(--sn-accent); box-shadow: none; }
        .sn-nav-secao { text-transform: uppercase; font-size: .72rem; font-weight: 700; letter-spacing: .06em; color: var(--sn-nav-muted); padding: 1rem 1rem .35rem; }
        .sn-nav a { display: flex; align-items: center; gap: .6rem; color: var(--sn-nav-text); text-decoration: none; padding: .42rem 1rem; border-left: 3px solid transparent; font-size: .9rem; }
        .sn-nav a:hover { background: var(--sn-nav-hover); color: #fff; }
        .sn-nav a.ativo { background: var(--sn-nav-hover); color: #fff; border-left-color: var(--sn-accent); font-weight: 600; }
        .sn-nav a i { width: 1.1rem; text-align: center; opacity: .85; }

        /* Área de conteúdo */
        .sn-main { flex: 1; min-width: 0; padding: 1.25rem; }
        @media (min-width: 1200px) { .sn-main { padding: 1.5rem 2rem; } }
        .card { border: 1px solid var(--sn-border); border-radius: .25rem; box-shadow: none !important; }
        .card-header { background: #f7f9fa; border-bottom-color: var(--sn-border); }

        /* Barra de título de lista/formulário */
        .sn-titulo { background: #fff; border: 1px solid var(--sn-border); border-radius: .25rem; padding: .6rem 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-bottom: 1rem; }
        .sn-titulo h1 { font-size: 1.15rem; font-weight: 700; margin: 0; }
        .sn-titulo .sn-tipo { color: var(--sn-muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }

        /* Listas */
        .sn-lista thead th { background: #f7f9fa; font-size: .8rem; font-weight: 700; color: var(--sn-label); text-transform: none; white-space: nowrap; border-bottom: 2px solid var(--sn-border); }
        .sn-lista thead th a { color: inherit; text-decoration: none; }
        .sn-lista td { font-size: .9rem; padding-top: .45rem; padding-bottom: .45rem; }
        .sn-lista tbody tr:hover td { background: #f3f8fc; }
        .sn-numero { font-weight: 600; white-space: nowrap; }
        .sn-trilha { font-size: .85rem; color: var(--sn-muted); }
        .sn-trilha .filtro { background: #e7eef4; border-radius: 3px; padding: .05rem .45rem; color: var(--sn-text); }

        /* Indicadores */
        .sn-ponto { display: inline-block; width: .6rem; height: .6rem; border-radius: 50%; margin-right: .4rem; vertical-align: middle; }
        .sn-prio-1 { background: #d6292f; } .sn-prio-2 { background: #f28a1a; } .sn-prio-3 { background: #f2c94c; }
        .sn-prio-4 { background: #6fa8dc; } .sn-prio-5 { background: #b4bfc6; }
        .sn-estado { font-size: .78rem; font-weight: 600; padding: .2rem .5rem; border-radius: 3px; white-space: nowrap; }

        /* Formulário em 2 colunas (rótulo à esquerda) */
        .sn-campo { display: grid; grid-template-columns: 38% 1fr; align-items: center; gap: .75rem; margin-bottom: .6rem; }
        .sn-campo > label, .sn-campo > .rotulo { text-align: right; color: var(--sn-label); font-size: .875rem; margin: 0; }
        .sn-campo .obrigatorio::before { content: '*'; color: #d6292f; margin-right: .2rem; }
        .sn-campo .valor { padding: .3rem .1rem; min-height: 1.9rem; }
        .sn-campo-largo { grid-template-columns: 19% 1fr; }
        @media (max-width: 767.98px) {
            .sn-campo, .sn-campo-largo { grid-template-columns: 1fr; gap: .2rem; }
            .sn-campo > label, .sn-campo > .rotulo { text-align: left; }
        }
        .form-control[readonly] { background: #f3f5f7; }
        .sn-secao { font-size: .95rem; font-weight: 700; border-bottom: 1px solid var(--sn-border); padding-bottom: .4rem; margin: 1.25rem 0 .9rem; }

        /* Atividades */
        .sn-atividade { border: 1px solid var(--sn-border); border-left: 4px solid var(--sn-comentario); border-radius: .25rem; background: #fff; padding: .65rem .85rem; margin-bottom: .75rem; }
        .sn-atividade.nota { border-left-color: var(--sn-nota); background: #fffdf3; }
        .sn-atividade .cab { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: var(--sn-muted); }
        .sn-atividade .cab strong { color: var(--sn-text); }
        .sn-atividade .mudanca { font-size: .85rem; color: var(--sn-label); margin-top: .35rem; }
        .sn-atividade .texto { white-space: pre-line; margin-top: .35rem; }
        .sn-tipo-tab .btn-check:checked + .btn { background: var(--sn-header); border-color: var(--sn-header); color: #fff; }

        /* Painel */
        .sn-kpi { background: #fff; border: 1px solid var(--sn-border); border-radius: .25rem; padding: .9rem 1rem; height: 100%; display: block; color: inherit; text-decoration: none; border-top: 3px solid var(--sn-primary); }
        .sn-kpi:hover { background: #f7fbff; }
        .sn-kpi .numero { font-size: 2rem; font-weight: 700; line-height: 1.1; }
        .sn-kpi .rotulo { color: var(--sn-muted); font-size: .85rem; }
        .sn-barra { height: .55rem; background: #e7ecf0; border-radius: 3px; overflow: hidden; }
        .sn-barra > span { display: block; height: 100%; }

        /* Telas de visitante */
        .sn-visitante { background: radial-gradient(circle at 20% 0%, #164a63 0%, var(--sn-header) 55%); min-height: 100vh; }
    </style>
</head>
@auth
@php
    $u = auth()->user();
    $iniciais = collect(explode(' ', $u->name))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    // Marca o item do menu que corresponde à tela/fila atual.
    $ativo = function (string $rota, array $params = []) {
        if (! request()->routeIs($rota)) return '';
        foreach (['fila', 'status'] as $chave) {
            if ((string) request($chave) !== (string) ($params[$chave] ?? '')) return '';
        }
        return 'ativo';
    };
@endphp
<body>
<header class="sn-header d-flex align-items-center gap-3 px-3">
    <button class="btn btn-link d-lg-none p-0 fs-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#navegador" aria-controls="navegador" aria-label="Abrir menu">
        <i class="bi bi-list"></i>
    </button>
    <a class="sn-brand" href="{{ route('dashboard') }}"><span class="marca"><i class="bi bi-headset"></i></span>HelpDesk TI</a>
    <form action="{{ route('chamados.index') }}" method="GET" class="sn-busca flex-grow-1 d-none d-md-block ms-3" role="search">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-search"></i></span>
            <input class="form-control rounded" type="search" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar incidentes (ex.: INC0010003)" aria-label="Pesquisar incidentes">
        </div>
    </form>
    <div class="ms-auto dropdown">
        <button class="btn btn-link d-flex align-items-center gap-2 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="sn-avatar">{{ $iniciais }}</span>
            <span class="d-none d-sm-inline text-start lh-sm">
                <span class="d-block small fw-semibold">{{ $u->name }}</span>
                <span class="d-block text-white-50" style="font-size:.75rem">{{ $u->perfil_label }}@if($u->grupo) · {{ $u->grupo->nome }}@endif</span>
            </span>
            <i class="bi bi-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><span class="dropdown-item-text small text-secondary">{{ $u->email }}</span></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sair</button>
                </form>
            </li>
        </ul>
    </div>
</header>

<div class="sn-shell">
    <nav class="sn-nav offcanvas-lg offcanvas-start" tabindex="-1" id="navegador" aria-label="Navegador">
        <div class="offcanvas-header d-lg-none">
            <span class="fw-semibold text-white">Navegador</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#navegador" aria-label="Fechar"></button>
        </div>
        <div class="w-100 pb-4">
            <div class="p-3 pb-1">
                <input type="search" class="form-control form-control-sm sn-nav-filtro" placeholder="Filtrar navegador" aria-label="Filtrar navegador" id="filtro-navegador">
            </div>

            <div class="sn-nav-grupo">
                <div class="sn-nav-secao">Autoatendimento</div>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'ativo' : '' }}"><i class="bi bi-speedometer2"></i> Painel</a>
                <a href="{{ route('chamados.create') }}" class="{{ request()->routeIs('chamados.create') ? 'ativo' : '' }}"><i class="bi bi-plus-circle"></i> Registrar incidente</a>
                @unless ($u->podeAtender())
                    <a href="{{ route('chamados.index') }}" class="{{ $ativo('chamados.index') }}"><i class="bi bi-person-lines-fill"></i> Meus incidentes</a>
                    <a href="{{ route('chamados.index', ['fila' => 'pendentes']) }}" class="{{ $ativo('chamados.index', ['fila' => 'pendentes']) }}"><i class="bi bi-hourglass-split"></i> Em aberto</a>
                @endunless
            </div>

            @can('atender-chamados')
            <div class="sn-nav-grupo">
                <div class="sn-nav-secao">Incidentes</div>
                <a href="{{ route('chamados.index', ['fila' => 'meus']) }}" class="{{ $ativo('chamados.index', ['fila' => 'meus']) }}"><i class="bi bi-person-check"></i> Atribuídos a mim</a>
                @if ($u->grupo_id)
                    <a href="{{ route('chamados.index', ['fila' => 'grupo']) }}" class="{{ $ativo('chamados.index', ['fila' => 'grupo']) }}"><i class="bi bi-people"></i> Do meu grupo</a>
                @endif
                <a href="{{ route('chamados.index', ['fila' => 'sem_atribuicao']) }}" class="{{ $ativo('chamados.index', ['fila' => 'sem_atribuicao']) }}"><i class="bi bi-inbox"></i> Sem atribuição</a>
                <a href="{{ route('chamados.index', ['fila' => 'pendentes']) }}" class="{{ $ativo('chamados.index', ['fila' => 'pendentes']) }}"><i class="bi bi-hourglass-split"></i> Abertos</a>
                <a href="{{ route('chamados.index', ['status' => 'resolvido']) }}" class="{{ $ativo('chamados.index', ['status' => 'resolvido']) }}"><i class="bi bi-check2-circle"></i> Resolvidos</a>
                <a href="{{ route('chamados.index') }}" class="{{ $ativo('chamados.index') }}"><i class="bi bi-list-ul"></i> Todos</a>
            </div>
            @endcan

            @can('admin')
            <div class="sn-nav-grupo">
                <div class="sn-nav-secao">Administração</div>
                <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'ativo' : '' }}"><i class="bi bi-person-gear"></i> Usuários</a>
                <a href="{{ route('grupos.index') }}" class="{{ request()->routeIs('grupos.*') ? 'ativo' : '' }}"><i class="bi bi-diagram-3"></i> Grupos de atribuição</a>
                <a href="{{ route('categorias.index') }}" class="{{ request()->routeIs('categorias.*') ? 'ativo' : '' }}"><i class="bi bi-tags"></i> Categorias</a>
            </div>
            @endcan
        </div>
    </nav>

    <main class="sn-main">
        @include('layouts._alertas')
        @yield('content')
    </main>
</div>
@else
<body class="sn-visitante">
<main class="container py-4">
    @include('layouts._alertas')
    @yield('content')
</main>
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
    // Filtro do navegador lateral: esconde itens que não combinam com o texto digitado.
    document.getElementById('filtro-navegador')?.addEventListener('input', function () {
        const termo = this.value.trim().toLowerCase();
        document.querySelectorAll('.sn-nav-grupo').forEach(function (grupo) {
            let visiveis = 0;
            grupo.querySelectorAll('a').forEach(function (link) {
                const mostra = link.textContent.toLowerCase().includes(termo);
                link.classList.toggle('d-none', !mostra);
                if (mostra) visiveis++;
            });
            grupo.classList.toggle('d-none', visiveis === 0);
        });
    });
</script>
@stack('scripts')
</body>
</html>
