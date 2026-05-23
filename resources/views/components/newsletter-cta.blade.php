<div class="blog-newsletter">
    <div class="bn-copy">
        <h2>El comercio local que crece<br>lee esto cada semana.</h2>
        <p>Casos de éxito reales, guías prácticas y datos sobre comercio de proximidad. Sin spam. Solo lo que funciona.</p>
    </div>
    <div class="bn-form-wrap">
        <form action="{{ url('/newsletter/suscribir') }}" method="POST" class="bn-form">
            @csrf
            <input type="hidden" name="fuente" value="{{ $fuente ?? 'newsletter' }}">
            <input class="bn-input{{ $errors->has('nombre') ? ' bn-input-err' : '' }}"
                   type="text" name="nombre" placeholder="Tu nombre"
                   value="{{ old('nombre') }}" autocomplete="given-name">
            <input class="bn-input{{ $errors->has('email') ? ' bn-input-err' : '' }}"
                   type="email" name="email" placeholder="tu@email.com"
                   value="{{ old('email') }}" autocomplete="email">
            <button type="submit" class="bn-btn">&#x2709; Suscribirme gratis</button>
        </form>
        @if($errors->has('nombre') || $errors->has('email'))
        <div class="bn-msg bn-msg-err">
            {{ $errors->first('nombre') ?: $errors->first('email') }}
        </div>
        @endif
    </div>
</div>

@php
$nlStatus = session('newsletter_status');
$nlMensajes = [
    'pendiente'   => ['titulo' => '¡Casi listo!',         'texto' => 'Te hemos enviado un email de confirmación. Ábrelo y pulsa el enlace para activar tu suscripción.'],
    'reactivado'  => ['titulo' => '¡Bienvenido de nuevo!','texto' => 'Ya vuelves a estar en la lista. Recibirás los próximos artículos en tu bandeja.'],
    'ya_suscrito' => ['titulo' => '¡Ya estás apuntado!',  'texto' => 'Tu email ya está en nuestra lista. ¡Gracias por seguir con nosotros!'],
];
@endphp

@if($nlStatus && isset($nlMensajes[$nlStatus]))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon:                'success',
        title:               @json($nlMensajes[$nlStatus]['titulo']),
        text:                @json($nlMensajes[$nlStatus]['texto']),
        confirmButtonText:   'Entendido',
        confirmButtonColor:  '#6d007e',
    });
});
</script>
@endpush
@endif
