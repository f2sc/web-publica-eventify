<header>
    <nav class="site-nav" aria-label="Navegación principal">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="nav-logo" aria-label="Eventify — Inicio">
                <img src="{{ asset('images/logo-horizontal-con-icono.png') }}" alt="Eventify" width="130" height="34">
            </a>

            <button class="nav-toggle" aria-controls="nav-menu" aria-expanded="false" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>

            <ul id="nav-menu" class="nav-menu" role="list">
                <li class="nav-home"><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Inicio</a></li>
                <li><a href="{{ url('/como-funciona') }}" class="{{ request()->is('como-funciona') ? 'active' : '' }}">Cómo funciona</a></li>
                <li class="nav-has-drop {{ request()->is('para-comercios') || request()->is('localidades*') ? 'active' : '' }}">
                    <a href="{{ url('/para-comercios') }}" class="{{ request()->is('para-comercios') || request()->is('localidades*') ? 'active' : '' }}">Para comercios <span class="nav-caret">&#x25BE;</span></a>
                    <ul class="nav-drop" role="list">
                        <li><a href="{{ url('/para-comercios') }}" class="{{ request()->is('para-comercios') ? 'active' : '' }}">Para comercios</a></li>
                        <li><a href="{{ url('/localidades') }}" class="{{ request()->is('localidades*') ? 'active' : '' }}">Localidades</a></li>
                    </ul>
                </li>
                <li><a href="{{ url('/para-asociaciones') }}" class="{{ request()->is('para-asociaciones') ? 'active' : '' }}">Para asociaciones</a></li>
                <li><a href="{{ url('/blog') }}" class="{{ request()->is('blog*') ? 'active' : '' }}">Blog</a></li>
            </ul>

            <div style="display:flex;gap:0.625rem;align-items:center;">
                <a href="{{ $appUrl }}/login" class="btn-nav-login">Entrar</a>
                <a href="{{ $appUrl }}/register?source=web-nav" class="btn-nav-cta">
                    &#x2B; Crear mi cuenta gratis
                </a>
            </div>
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
