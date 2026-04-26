@extends('layouts.admin')

@section('title', 'Nuevo artículo')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nuevo artículo</h1>
    <a href="{{ route('admin.articulos.index') }}" class="btn btn-secondary">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.articulos.store') }}" class="card">
    @csrf
    @include('admin.articulos._form')
    <div style="margin-top:1.5rem">
        <button type="submit" class="btn btn-primary">Guardar artículo</button>
    </div>
</form>
@endsection
