<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="google-site-verification" content="cx_TUKZU4cTiI00PbrqqMpL0FJV4JfDLXwCewraVEzM">
    <x-seo-head
        :title="$title ?? 'Inicio'"
        :description="$description ?? 'Eventify — Fidelización QR para el comercio local'"
        :canonical="$canonical ?? url()->current()"
        :schema="$schema ?? null"
        :indexable="$indexable ?? true"
        :ogImage="$ogImage ?? null"
        :paginator="$paginator ?? null"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
