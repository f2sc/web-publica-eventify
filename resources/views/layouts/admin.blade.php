<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel CMS') | Eventify Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('head')
</head>
<body class="admin-body">
    @if(session()->has('cms_token'))
    <header class="admin-header">
        <div class="admin-header-inner">
            <a href="{{ route('admin.articulos.index') }}" class="admin-logo">
                Eventify <span>Admin</span>
            </a>
            <nav aria-label="Menú admin">
                <ul class="admin-nav">
                    <li><a href="{{ route('admin.calendario.index') }}" class="{{ request()->is('admin/calendario*') ? 'active' : '' }}">📅 Calendario</a></li>
                    <li><a href="{{ route('admin.articulos.index') }}" class="{{ request()->is('admin/articulos*') && !request()->is('admin/articulos/*/ai*') ? 'active' : '' }}">Artículos</a></li>
                    <li><a href="{{ route('admin.categorias.index') }}" class="{{ request()->is('admin/categorias*') ? 'active' : '' }}">Categorías</a></li>
                    <li><a href="{{ route('admin.ia.config') }}" class="{{ request()->is('admin/ia*') ? 'active' : '' }}">✦ Configuración IA</a></li>
                </ul>
            </nav>
            <form action="{{ route('admin.logout') }}" method="POST" class="admin-logout">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </header>
    @endif

    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
