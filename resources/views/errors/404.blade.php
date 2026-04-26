@extends('layouts.app', [
    'title'       => 'Página no encontrada',
    'description' => 'La página que buscas no existe o ha sido movida.',
    'canonical'   => url()->current(),
    'indexable'   => false,
])

@section('content')
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1>Página no encontrada</h1>
            <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Ir al inicio</a>
                <a href="{{ url('/localidades') }}" class="btn btn-secondary">Ver localidades</a>
            </div>
        </div>
    </div>
</section>
@endsection
