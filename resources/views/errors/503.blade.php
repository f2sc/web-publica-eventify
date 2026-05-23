@extends('layouts.app', [
    'title'       => 'Volvemos enseguida',
    'description' => 'La web estará disponible en unos segundos.',
    'canonical'   => url()->current(),
    'indexable'   => false,
])

@section('content')
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code" style="font-size:3rem">🔧</div>
            <h1>Volvemos enseguida</h1>
            <p>Estamos actualizando la web con novedades. Estará lista en unos segundos — recarga la página.</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Recargar</a>
            </div>
        </div>
    </div>
</section>
@endsection
