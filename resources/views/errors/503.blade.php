@extends('layouts.app', [
    'title'       => 'Servicio temporalmente no disponible',
    'description' => 'Estamos teniendo problemas técnicos. Vuelve en unos minutos.',
    'canonical'   => url()->current(),
    'indexable'   => false,
])

@section('content')
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">503</div>
            <h1>Servicio temporalmente no disponible</h1>
            <p>Estamos experimentando problemas técnicos. Por favor, vuelve en unos minutos.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Ir al inicio</a>
            </div>
        </div>
    </div>
</section>
@endsection
