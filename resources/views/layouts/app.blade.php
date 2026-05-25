<!DOCTYPE html>
<html lang="es">
<head>
    @if(app()->isProduction())
    <!-- Google tag (gtag.js) — Consent Mode v2 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-4XCX84X3N6"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        wait_for_update: 500
      });
      gtag('js', new Date());
      gtag('config', 'G-4XCX84X3N6');
      if (localStorage.getItem('cookie_consent') === 'accepted') {
        gtag('consent', 'update', { analytics_storage: 'granted' });
      }
    </script>
    @endif
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
    @if(app()->isProduction())
    <x-cookie-banner />
    @endif

    @stack('scripts')
</body>
</html>
