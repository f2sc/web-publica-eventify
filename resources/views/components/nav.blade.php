<header>
    <nav class="site-nav" aria-label="Navegación principal">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="nav-logo" aria-label="Eventify — Inicio">
                <img src="{{ asset('images/logo-horizontal-con-icono.png') }}" alt="Eventify" width="140" height="36">
            </a>

            <button class="nav-toggle" aria-controls="nav-menu" aria-expanded="false" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>

            <ul id="nav-menu" class="nav-menu" role="list">
                <li><a href="{{ url('/como-funciona') }}" class="{{ request()->is('como-funciona') ? 'active' : '' }}">Cómo funciona</a></li>
                <li><a href="{{ url('/para-comercios') }}" class="{{ request()->is('para-comercios') ? 'active' : '' }}">Para comercios</a></li>
                <li><a href="{{ url('/para-asociaciones') }}" class="{{ request()->is('para-asociaciones') ? 'active' : '' }}">Para asociaciones</a></li>
                <li><a href="{{ url('/blog') }}" class="{{ request()->is('blog*') ? 'active' : '' }}">Blog</a></li>
                <li><a href="{{ url('/localidades') }}" class="{{ request()->is('localidades*') ? 'active' : '' }}">Localidades</a></li>
            </ul>

            <a href="https://app.eventify.es/qr?source=web-nav" class="btn-nav-cta">
                Crear mi QR gratis
            </a>
        </div>
    </nav>
</header>

<script>
    document.querySelector('.nav-toggle').addEventListener('click', function () {
        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !expanded);
        document.getElementById('nav-menu').classList.toggle('open');
    });
</script>
