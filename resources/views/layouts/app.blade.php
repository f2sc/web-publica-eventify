<!DOCTYPE html>
<html lang="es">
<head>
    <x-seo-head
        :title="$title ?? 'Inicio'"
        :description="$description ?? 'Eventify — Fidelización QR para el comercio local'"
        :canonical="$canonical ?? url()->current()"
        :schema="$schema ?? null"
        :indexable="$indexable ?? true"
        :ogImage="$ogImage ?? null"
    />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <x-nav />

    <main id="main-content">
        @yield('content')
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
