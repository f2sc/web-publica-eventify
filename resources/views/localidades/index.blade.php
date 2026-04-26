@extends('layouts.app')

@section('content')

<section class="hero">
    <div class="hero-container">
        <h1>Comercios locales adheridos a Eventify</h1>
        <p>Encuentra tu localidad y descubre los comercios con programa de fidelización.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if(count($localidades) > 0)
        <ul class="localidades-list" role="list">
            @foreach($localidades as $loc)
            @php
                $nombre = $loc['nombre'] ?? $loc['name'] ?? '';
                $slug   = $loc['slug'] ?? '';
                $num    = $loc['num_comercios'] ?? 0;
            @endphp
            <li>
                <a href="{{ url('/localidades/' . $slug) }}" class="localidad-row">
                    <span class="localidad-nombre">{{ $nombre }}</span>
                    @if($num)
                    <span class="localidad-count">{{ $num }} {{ $num === 1 ? 'comercio' : 'comercios' }}</span>
                    @endif
                    <span class="localidad-arrow">›</span>
                </a>
            </li>
            @endforeach
        </ul>
        @else
        <p style="text-align:center;color:#6b7280;padding:3rem 0">No hay localidades disponibles aún.</p>
        @endif
    </div>
</section>

@endsection

@push('head')
<style>
.localidades-list { max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 0.5rem; }
.localidad-row { display: flex; align-items: center; gap: 1rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; transition: all .2s; }
.localidad-row:hover { border-color: #6c3fc5; box-shadow: 0 2px 8px rgba(108,63,197,.1); }
.localidad-nombre { font-weight: 600; flex: 1; }
.localidad-count { font-size: 0.85rem; color: #6b7280; }
.localidad-arrow { color: #6c3fc5; font-size: 1.1rem; }
</style>
@endpush
