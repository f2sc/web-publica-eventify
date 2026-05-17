@extends('layouts.admin')
@section('title', 'Editar categoría')

@section('content')
<div class="page-header">
    <h1 class="page-title">Editar: {{ $categoria->nombre }}</h1>
    <div style="display:flex;gap:.75rem">
        <a href="{{ url('/blog/categoria/' . $categoria->slug) }}" target="_blank" class="btn btn-secondary" style="font-size:.85rem">
            Ver en el blog ↗
        </a>
        <a href="{{ route('admin.categorias.index') }}" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.categorias.update', $categoria) }}" class="card">
    @csrf @method('PUT')
    @include('admin.categorias._form', ['categoria' => $categoria])
    <div style="margin-top:1.5rem">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
