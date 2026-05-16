@extends('layouts.app')

@section('content')
<div class="sub-hero">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt" style="text-align:center;">
        <div style="font-size:3rem;margin-bottom:1rem;">🎉</div>
        <h1>¡Ya estás dentro, {{ $nombre }}!</h1>
        <p>Tu suscripción al blog de Eventify está confirmada. Te avisaremos cada vez que publiquemos algo que valga la pena leer.</p>
        <div style="margin-top:2rem;display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ url('/blog') }}" class="btn btn-accent">Ver el blog →</a>
            <a href="{{ url('/') }}" class="btn" style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.3);">Volver al inicio</a>
        </div>
    </div>
</div>
@endsection
