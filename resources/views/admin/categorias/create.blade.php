@extends('layouts.admin')
@section('title', 'Nueva categoría')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nueva categoría</h1>
    <a href="{{ route('admin.categorias.index') }}" class="btn btn-secondary">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.categorias.store') }}" class="card">
    @csrf
    @include('admin.categorias._form')
    <div style="margin-top:1.5rem">
        <button type="submit" class="btn btn-primary">Guardar categoría</button>
    </div>
</form>
@endsection
