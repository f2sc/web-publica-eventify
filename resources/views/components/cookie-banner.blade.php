<div id="cookie-banner" role="dialog" aria-label="Aviso de cookies" style="display:none;">
    <div class="cb-inner">
        <p class="cb-text">
            Usamos cookies analíticas (Google Analytics) para entender cómo se usa la web y mejorarla.
            <a href="{{ url('/cookies') }}" class="cb-link">Más información</a>
        </p>
        <div class="cb-actions">
            <button id="cb-reject" class="cb-btn cb-btn-secondary">Solo esenciales</button>
            <button id="cb-accept" class="cb-btn cb-btn-primary">Aceptar</button>
        </div>
    </div>
</div>

<style>
#cookie-banner {
    position: fixed;
    bottom: 1.25rem;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 2rem);
    max-width: 720px;
    background: #1f2937;
    color: #f9fafb;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.35);
    z-index: 9999;
    padding: 1rem 1.25rem;
}
.cb-inner {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
}
.cb-text {
    flex: 1;
    font-size: .875rem;
    line-height: 1.5;
    margin: 0;
    color: #d1d5db;
}
.cb-link {
    color: #a78bfa;
    text-decoration: underline;
    white-space: nowrap;
}
.cb-actions {
    display: flex;
    gap: .6rem;
    flex-shrink: 0;
}
.cb-btn {
    padding: .5rem 1.1rem;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    white-space: nowrap;
}
.cb-btn-primary {
    background: var(--brand, #7c3aed);
    color: #fff;
}
.cb-btn-primary:hover { opacity: .9; }
.cb-btn-secondary {
    background: transparent;
    color: #d1d5db;
    border: 1px solid #4b5563;
}
.cb-btn-secondary:hover { background: #374151; }
@media(max-width:540px) {
    .cb-inner { flex-direction: column; align-items: flex-start; }
    .cb-actions { width: 100%; }
    .cb-btn { flex: 1; text-align: center; }
}
</style>

<script>
(function () {
    var consent = localStorage.getItem('cookie_consent');
    if (consent) return;

    var banner = document.getElementById('cookie-banner');
    banner.style.display = 'block';

    document.getElementById('cb-accept').addEventListener('click', function () {
        localStorage.setItem('cookie_consent', 'accepted');
        gtag('consent', 'update', { analytics_storage: 'granted' });
        banner.style.display = 'none';
    });

    document.getElementById('cb-reject').addEventListener('click', function () {
        localStorage.setItem('cookie_consent', 'rejected');
        banner.style.display = 'none';
    });
})();
</script>
