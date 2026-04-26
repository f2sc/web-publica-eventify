@extends('layouts.admin')

@section('title', 'Artículos')

@section('content')
<div class="page-header">
    <h1 class="page-title">Artículos del blog</h1>
    <a href="{{ route('admin.articulos.create') }}" class="btn btn-primary">+ Nuevo artículo</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Estado</th>
                <th>Publicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articulos as $articulo)
            <tr>
                <td>
                    <strong>{{ $articulo->titulo }}</strong>
                    @if($articulo->categoria_blog)
                    <br><small style="color:#9ca3af">{{ $articulo->categoria_blog }}</small>
                    @endif
                </td>
                <td>
                    @if($articulo->estado === 'publicado')
                    <span class="badge badge-green">Publicado</span>
                    @elseif($articulo->estado === 'borrador')
                    <span class="badge badge-yellow">Borrador</span>
                    @else
                    <span class="badge badge-gray">Archivado</span>
                    @endif
                </td>
                <td>
                    @if($articulo->fecha_publicacion)
                    {{ $articulo->fecha_publicacion->format('d/m/Y') }}
                    @else
                    <span style="color:#9ca3af">—</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:0.5rem">
                        <a href="{{ route('admin.articulos.edit', $articulo) }}" class="btn btn-secondary">Editar</a>
                        <form method="POST" action="{{ route('admin.articulos.destroy', $articulo) }}"
                              onsubmit="return confirm('¿Eliminar este artículo?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Borrar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;color:#9ca3af;padding:2rem">
                    No hay artículos aún. <a href="{{ route('admin.articulos.create') }}">Crea el primero</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($articulos->hasPages())
    <div style="padding:1rem;border-top:1px solid #e5e7eb">
        {{ $articulos->links() }}
    </div>
    @endif
</div>
@endsection
