@extends('layouts.admin')

@section('title', 'Editar artículo')

@section('content')
<div class="page-header">
    <h1 class="page-title">Editar: {{ $articulo->titulo }}</h1>
    <a href="{{ route('admin.articulos.index') }}" class="btn btn-secondary">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.articulos.update', $articulo) }}" class="card">
    @csrf @method('PUT')
    @include('admin.articulos._form', ['articulo' => $articulo])
    <div style="margin-top:1.5rem">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
