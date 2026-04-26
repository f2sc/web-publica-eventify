<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="{{ url('/') }}" aria-label="Eventify — Inicio">
                <img src="{{ asset('images/logo-horizontal-con-icono-white.png') }}" alt="Eventify" width="120" height="32" loading="lazy">
            </a>
            <p>Fidelización QR para el comercio local.</p>
        </div>

        <nav aria-label="Links del pie de página">
            <div class="footer-cols">
                <div class="footer-col">
                    <h3>Plataforma</h3>
                    <ul>
                        <li><a href="{{ url('/como-funciona') }}">Cómo funciona</a></li>
                        <li><a href="{{ url('/para-comercios') }}">Para comercios</a></li>
                        <li><a href="{{ url('/para-asociaciones') }}">Para asociaciones</a></li>
                        <li><a href="https://app.eventify.es/qr?source=footer">Registrar comercio</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Directorio</h3>
                    <ul>
                        <li><a href="{{ url('/localidades') }}">Localidades</a></li>
                        <li><a href="{{ url('/categorias/restaurantes') }}">Restaurantes</a></li>
                        <li><a href="{{ url('/categorias/bares') }}">Bares</a></li>
                        <li><a href="{{ url('/categorias/peluquerias') }}">Peluquerías</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contenido</h3>
                    <ul>
                        <li><a href="{{ url('/blog') }}">Blog</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Eventify. Todos los derechos reservados.</p>
    </div>
</footer>
