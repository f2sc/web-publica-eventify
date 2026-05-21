@extends('layouts.admin')

@section('title', 'Editar artículo')

@section('content')
<div class="page-header">
    <h1 class="page-title">Editar artículo</h1>
    <div style="display:flex;gap:.5rem;align-items:center">
        @php
            $estaVivo = $articulo->estado === 'publicado'
                && (!$articulo->fecha_publicacion || $articulo->fecha_publicacion->lte(now()));
        @endphp
        @if($articulo->estado === 'publicado')
        <a href="{{ $estaVivo ? route('blog.show', $articulo->slug) : route('admin.articulos.preview', $articulo) }}"
           target="_blank" class="btn btn-secondary" style="display:flex;align-items:center;gap:.3rem">
            ↗ Ver en blog
        </a>
        @else
        <a href="{{ route('admin.articulos.preview', $articulo) }}" target="_blank"
           class="btn btn-secondary" style="display:flex;align-items:center;gap:.3rem;background:#fefce8;border-color:#fde047;color:#854d0e">
            👁 Vista previa
        </a>
        @endif
        <a href="{{ route('admin.articulos.index') }}" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.articulos.update', $articulo) }}" id="article-form">
    @csrf @method('PUT')
    @include('admin.articulos._form', ['articulo' => $articulo])
</form>
@endsection
