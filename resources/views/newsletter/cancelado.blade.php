@extends('layouts.app')

@section('content')
<div class="sub-hero">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt" style="text-align:center;">
        <div style="font-size:3rem;margin-bottom:1rem;">👋</div>
        <h1>Te hemos dado de baja.</h1>
        <p>No recibirás más emails del blog de Eventify. Si cambiaste de opinión, puedes volver a suscribirte cuando quieras.</p>
        <div style="margin-top:2rem;">
            <a href="{{ url('/blog') }}" class="btn btn-accent">Volver al blog →</a>
        </div>
    </div>
</div>
@endsection
