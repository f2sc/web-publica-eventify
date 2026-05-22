@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background-image:url('{{ asset('images/big/asociacion-gestora-comercios-barrio-eventify.png') }}');background-position:center 25%;"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Para asociaciones y ayuntamientos</div>
        <h1>La infraestructura digital<br>del comercio de proximidad.</h1>
        <p>Gestiona todos tus comercios, lanza campañas conjuntas y demuestra tu impacto.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem;">
            <a href="{{ $appUrl }}/register?source=para-asociaciones" class="btn btn-accent">Solicitar demo</a>
        </div>
    </div>
</div>

<section class="section" style="background:#fff;">
    <div class="container">
        <div class="benefits-grid">
            <div>
                <div class="eyebrow">Modelo B2B2G</div>
                <h2 class="section-title">Una plataforma para<br>toda la asociación.</h2>
                <p style="color:#6b7280;margin-bottom:2rem">No es solo para comercios individuales. Las asociaciones tienen su propio panel para gestionar todos sus miembros y justificar su impacto.</p>
                <div class="asoc-cards">
                    <div class="asoc-card">
                        <div class="asoc-ico">&#x1F465;</div>
                        <div>
                            <h3>Onboarding de todos tus comercios</h3>
                            <p>Incorpora a todos los asociados en pocos días. El equipo de Eventify os acompaña.</p>
                        </div>
                    </div>
                    <div class="asoc-card">
                        <div class="asoc-ico">&#x1F4E3;</div>
                        <div>
                            <h3>Campañas conjuntas de la asociación</h3>
                            <p>Llega a los clientes de TODOS los comercios a la vez. El impacto es exponencial.</p>
                        </div>
                    </div>
                    <div class="asoc-card">
                        <div class="asoc-ico">&#x1F4CB;</div>
                        <div>
                            <h3>Reporting para el ayuntamiento</h3>
                            <p>Genera informes de actividad para justificar subvenciones y vuestro valor.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <img src="{{ asset('images/big/reunion-asociacion-comerciantes-tablet-eventify.png') }}"
                     alt="Reunión de asociación de comerciantes con tablet mostrando Eventify"
                     width="540" height="400" loading="lazy"
                     style="border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.12);width:100%">
            </div>
        </div>
    </div>
</section>

{{-- STATS --}}
@if(!empty($stats))
<section class="section" style="background:var(--surface);">
    <div class="container">
        <div class="eyebrow">En tiempo real</div>
        <h2 class="section-title">Eventify en números</h2>
        <div class="asoc-stats">
            @if(isset($stats['comercios']))
            <div class="astat"><div class="astat-n">{{ number_format($stats['comercios']) }}</div><div class="astat-l">Comercios activos</div></div>
            @endif
            @if(isset($stats['localidades']))
            <div class="astat"><div class="astat-n">{{ number_format($stats['localidades']) }}</div><div class="astat-l">Localidades activas</div></div>
            @endif
            @if(isset($stats['asociaciones']))
            <div class="astat"><div class="astat-n">{{ number_format($stats['asociaciones']) }}</div><div class="astat-l">Asociaciones</div></div>
            @endif
            @if(isset($stats['clientes']))
            <div class="astat"><div class="astat-n">{{ number_format($stats['clientes']) }}</div><div class="astat-l">Clientes registrados</div></div>
            @endif
            <div class="astat"><div class="astat-n">0&euro;</div><div class="astat-l">Para empezar</div></div>
        </div>
    </div>
</section>
@endif

{{-- DIRECTORIO DE ASOCIACIONES --}}
@if(count($asociaciones) > 0)
<section class="asoc-dir-sec">
    <div class="asoc-dir-header">
        <div class="eyebrow" style="justify-content:center;">&#x1F3DB; Asociaciones activas</div>
        <h2 class="section-title">Asociaciones que ya usan Eventify</h2>
        <p class="section-subtitle" style="margin:0 auto;">Haz clic en cualquier asociación para ver los comercios que forman parte de su red.</p>
    </div>
    <div class="asoc-grid">
        @foreach($asociaciones as $asoc)
        @php
            $asocNombre    = $asoc['nombre'] ?? '';
            $asocAcronimo  = $asoc['acronimo'] ?? strtoupper(mb_substr($asocNombre, 0, 2, 'UTF-8'));
            $asocLocalidad = $asoc['localidad'] ?? '';
            $asocProvincia = $asoc['provincia'] ?? '';
            $asocLoc       = trim(($asocLocalidad ? $asocLocalidad : '') . ($asocProvincia && $asocProvincia !== $asocLocalidad ? ', ' . $asocProvincia : ''));
            $asocComercios = $asoc['comercios'] ?? [];
            $asocGrads     = ['linear-gradient(135deg,#6d007e,#b12140)','linear-gradient(135deg,#b12140,#6d007e)','linear-gradient(135deg,#9d1060,#6d007e)','linear-gradient(135deg,#6d007e,#9d1060)'];
            $asocGrad      = $asocGrads[abs(crc32($asocNombre)) % count($asocGrads)];
        @endphp
        <div class="asoc-item" onclick="toggleAsoc(this)">
            <div class="asoc-item-head">
                <div class="asoc-ilogo" style="background:{{ $asocGrad }};">{{ $asocAcronimo }}</div>
                <div>
                    <div class="asoc-item-name">{{ $asocNombre }}</div>
                    @if($asocLoc)<div class="asoc-item-loc">{{ $asocLoc }}</div>@endif
                </div>
                <div class="asoc-item-right">
                    @if(count($asocComercios) > 0)
                    <span class="asoc-item-count">{{ count($asocComercios) }} {{ count($asocComercios) === 1 ? 'comercio' : 'comercios' }}</span>
                    @endif
                    <span class="asoc-chevron">&#x25BC;</span>
                </div>
            </div>
            @if(count($asocComercios) > 0)
            <div class="asoc-drawer"><div class="asoc-drawer-inner">
                @foreach($asocComercios as $com)
                @php
                    $comNombre = $com['nombre_comercial'] ?? $com['nombre'] ?? '';
                    $comCat    = $com['categoria']['nombre'] ?? '';
                    $comInits  = strtoupper(mb_substr($comNombre, 0, 2, 'UTF-8'));
                    $comGrads  = ['linear-gradient(135deg,#6d007e,#b12140)','linear-gradient(135deg,#b12140,#6d007e)','linear-gradient(135deg,#9d1060,#b12140)'];
                    $comGrad   = $comGrads[abs(crc32($comNombre)) % count($comGrads)];
                    $comCodigo = $com['codigo_comercio'] ?? ($com['slug'] ?? null);
                @endphp
                @if($comCodigo)
                <a href="{{ $appUrl }}/c/{{ $comCodigo }}" class="asoc-chip" target="_blank" rel="noopener">
                    @if(!empty($com['url_logo']))
                    <div class="asoc-chip-logo" style="background:#f5f5f5;overflow:hidden;padding:0;"><img src="{{ $com['url_logo'] }}" alt="{{ $comNombre }}" style="width:100%;height:100%;object-fit:cover;"></div>
                    @else
                    <div class="asoc-chip-logo" style="background:{{ $comGrad }};">{{ $comInits }}</div>
                    @endif
                    <div>
                        <div class="asoc-chip-name">{{ $comNombre }}</div>
                        @if($comCat)<div class="asoc-chip-type">{{ $comCat }}</div>@endif
                    </div>
                </a>
                @else
                <div class="asoc-chip">
                    @if(!empty($com['url_logo']))
                    <div class="asoc-chip-logo" style="background:#f5f5f5;overflow:hidden;padding:0;"><img src="{{ $com['url_logo'] }}" alt="{{ $comNombre }}" style="width:100%;height:100%;object-fit:cover;"></div>
                    @else
                    <div class="asoc-chip-logo" style="background:{{ $comGrad }};">{{ $comInits }}</div>
                    @endif
                    <div>
                        <div class="asoc-chip-name">{{ $comNombre }}</div>
                        @if($comCat)<div class="asoc-chip-type">{{ $comCat }}</div>@endif
                    </div>
                </div>
                @endif
                @endforeach
            </div></div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@else
<section class="asoc-dir-sec">
    <div class="asoc-dir-header" style="text-align:center;">
        <div class="eyebrow" style="justify-content:center;">&#x1F3DB; Asociaciones</div>
        <h2 class="section-title">&iquest;Gestionas una asociaci&oacute;n de comerciantes?</h2>
        <p class="section-subtitle" style="margin:0 auto 2rem;">Digitaliza el comercio de tu barrio o municipio. Gestiona todos tus miembros, lanza campa&ntilde;as conjuntas y demuestra tu impacto.</p>
        <a href="{{ $appUrl }}/register?source=para-asociaciones-empty" class="btn btn-primary">Registrar mi asociaci&oacute;n &rarr;</a>
    </div>
</section>
@endif

<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <h2>¿Vuestra asociación quiere dar el salto?</h2>
            <p>Os acompañamos en todo el proceso. Sin coste inicial. Contactad con nosotros y os preparamos una demo adaptada a vuestro caso.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/register?source=para-asociaciones-cta" class="btn btn-accent">Solicitar demo gratuita &rarr;</a>
            </div>
        </div>
    </div>
    <div class="cta-right">
        <img src="{{ asset('images/big/asociacion-comerciantes-reunion-datos-eventify.png') }}"
             alt="Asociación de comerciantes reunida con datos Eventify" loading="lazy" style="object-position:center 20%;">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleAsoc(el) {
    var isOpen = el.classList.contains('open');
    document.querySelectorAll('.asoc-item.open').forEach(function(item) { item.classList.remove('open'); });
    if (!isOpen) el.classList.add('open');
}
</script>
@endpush

@push('head')
<style>
.benefits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.asoc-cards { display: flex; flex-direction: column; gap: 1.25rem; }
.asoc-card { display: flex; gap: 1rem; align-items: flex-start; }
.asoc-ico { font-size: 1.4rem; flex-shrink: 0; line-height: 1.4; }
.asoc-card h3 { font-size: 0.95rem; font-weight: 700; color: var(--navy); margin: 0 0 0.25rem; }
.asoc-card p { color: #6b7280; font-size: 0.875rem; margin: 0; }
@media(max-width:768px){ .benefits-grid { grid-template-columns: 1fr; } }
</style>
@endpush
