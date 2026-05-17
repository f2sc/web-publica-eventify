@extends('layouts.admin')
@section('title', 'Categorías del blog')

@section('content')
<div class="page-header">
    <h1 class="page-title">Categorías del blog</h1>
    <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">+ Nueva categoría</a>
</div>

@if($categorias->isEmpty())
    <div class="card" style="text-align:center;padding:3rem;color:#9ca3af">
        No hay categorías todavía. <a href="{{ route('admin.categorias.create') }}">Crea la primera</a>.
    </div>
@else
<div class="card" style="padding:0">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Slug (URL)</th>
                <th>Artículos</th>
                <th>Meta description</th>
                <th style="width:130px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorias as $cat)
            <tr>
                <td><strong>{{ $cat->nombre }}</strong></td>
                <td><code style="font-size:.8rem;color:#6b7280">/blog/categoria/{{ $cat->slug }}</code></td>
                <td>
                    <span class="badge" style="background:#ede9fe;color:#6c3fc5">{{ $cat->articulos_count }}</span>
                </td>
                <td style="font-size:.8rem;color:#6b7280;max-width:260px">
                    {{ Str::limit($cat->meta_description, 80) }}
                </td>
                <td>
                    <div style="display:flex;gap:.5rem">
                        <a href="{{ route('admin.categorias.edit', $cat) }}" class="btn btn-secondary" style="padding:.35rem .75rem;font-size:.8rem">Editar</a>
                        <form method="POST" action="{{ route('admin.categorias.destroy', $cat) }}"
                              onsubmit="return confirm('¿Eliminar la categoría «{{ $cat->nombre }}»? Los artículos vinculados quedarán sin categoría.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn" style="padding:.35rem .75rem;font-size:.8rem;background:#fee2e2;color:#dc2626">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
