@extends('layouts.admin')

@section('title', 'Artículos')

@push('head')
<style>
.estado-sel {
    border: none; outline: none; cursor: pointer;
    font-size: .75rem; font-weight: 600; border-radius: 20px;
    padding: .2rem .65rem; appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b7280'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right .4rem center;
    padding-right: 1.4rem;
}
.estado-sel.s-publicado  { background-color:#d1fae5; color:#065f46; }
.estado-sel.s-borrador   { background-color:#fef9c3; color:#713f12; }
.estado-sel.s-programado { background-color:#dbeafe; color:#1e3a8a; }
.estado-sel.s-archivado  { background-color:#f3f4f6; color:#374151; }
.estado-sel:disabled { opacity:.55; cursor:wait; }
.btn-view {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.3rem .7rem; border-radius:6px; font-size:.78rem; font-weight:600;
    text-decoration:none; white-space:nowrap; border:1.5px solid;
}
.btn-view-pub  { background:#f0fdf4; color:#15803d; border-color:#86efac; }
.btn-view-prev { background:#fefce8; color:#854d0e; border-color:#fde047; }
</style>
@endpush

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
            <tr id="row-{{ $articulo->id }}" data-slug="{{ $articulo->slug }}">
                <td>
                    <strong>{{ $articulo->titulo }}</strong>
                    @if($articulo->categoria_blog)
                    <br><small style="color:#9ca3af">{{ $articulo->categoria_blog }}</small>
                    @endif
                </td>
                <td>
                    <select
                        class="estado-sel s-{{ $articulo->estado }}"
                        data-id="{{ $articulo->id }}"
                        data-prev="{{ $articulo->estado }}"
                        onchange="cambiarEstado(this)">
                        <option value="borrador"   {{ $articulo->estado === 'borrador'   ? 'selected' : '' }}>Borrador</option>
                        <option value="programado" {{ $articulo->estado === 'programado' ? 'selected' : '' }}>Programado</option>
                        <option value="publicado"  {{ $articulo->estado === 'publicado'  ? 'selected' : '' }}>Publicado</option>
                        <option value="archivado"  {{ $articulo->estado === 'archivado'  ? 'selected' : '' }}>Archivado</option>
                    </select>
                </td>
                <td>
                    @if($articulo->fecha_publicacion)
                    {{ $articulo->fecha_publicacion->format('d/m/Y') }}
                    @else
                    <span style="color:#9ca3af">—</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center">
                        @php
                            $estaVivo = $articulo->estado === 'publicado'
                                && (!$articulo->fecha_publicacion || $articulo->fecha_publicacion->lte(now()));
                        @endphp
                        @if($articulo->estado === 'publicado')
                        <a href="{{ $estaVivo ? route('blog.show', $articulo->slug) : route('admin.articulos.preview', $articulo) }}"
                           target="_blank" class="btn-view btn-view-pub">↗ Ver en blog</a>
                        @else
                        <a href="{{ route('admin.articulos.preview', $articulo) }}" target="_blank"
                           class="btn-view btn-view-prev">👁 Vista previa</a>
                        @endif
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

@push('scripts')
<script>
const ESTADO_URL = '{{ url("/admin/articulos") }}';
const CSRF_TOK   = document.querySelector('meta[name="csrf-token"]')?.content;

async function cambiarEstado(sel) {
    const id    = sel.dataset.id;
    const prev  = sel.dataset.prev;
    const nuevo = sel.value;
    sel.disabled = true;

    try {
        const r = await fetch(`${ESTADO_URL}/${id}/estado`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOK },
            body: JSON.stringify({ estado: nuevo }),
        });
        const res = await r.json();
        if (!res.ok) throw new Error(res.message || 'Error');

        sel.dataset.prev = nuevo;
        sel.className = `estado-sel s-${nuevo}`;

        // Tras cambio de estado, siempre mostrar preview (no podemos saber la fecha desde JS)
        const row     = document.getElementById(`row-${id}`);
        const btnView = row?.querySelector('.btn-view');
        if (btnView) {
            btnView.outerHTML = `<a href="${ESTADO_URL}/${id}/preview" target="_blank" class="btn-view btn-view-prev">👁 Vista previa</a>`;
        }
    } catch (e) {
        sel.value    = prev;
        sel.className = `estado-sel s-${prev}`;
        alert('Error al cambiar estado: ' + e.message);
    } finally {
        sel.disabled = false;
    }
}
</script>
@endpush
