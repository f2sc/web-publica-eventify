@extends('layouts.admin')

@section('title', 'Artículos')

@push('head')
<style>
/* ── Estado ── */
.estado-sel {
    border:none; outline:none; cursor:pointer;
    font-size:.75rem; font-weight:600; border-radius:20px;
    padding:.2rem .65rem; appearance:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b7280'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right .4rem center;
    padding-right:1.4rem;
}
.estado-sel.s-publicado  { background-color:#d1fae5; color:#065f46; }
.estado-sel.s-borrador   { background-color:#fef9c3; color:#713f12; }
.estado-sel.s-programado { background-color:#dbeafe; color:#1e3a8a; }
.estado-sel.s-archivado  { background-color:#f3f4f6; color:#374151; }
.estado-sel:disabled { opacity:.55; cursor:wait; }

/* ── Miniatura ── */
.art-thumb {
    width:52px; height:52px; object-fit:cover; border-radius:7px;
    cursor:zoom-in; display:block; border:1px solid #e5e7eb;
    transition:opacity .15s;
}
.art-thumb:hover { opacity:.82; }
.art-thumb-empty {
    width:52px; height:52px; border-radius:7px; border:1px dashed #d1d5db;
    background:#f9fafb; display:flex; align-items:center; justify-content:center;
    color:#d1d5db; font-size:1.2rem; flex-shrink:0;
}

/* ── Botones de acción ── */
.act-btns { display:flex; gap:.3rem; align-items:center; }
.act-btn {
    display:inline-flex; align-items:center; justify-content:center;
    width:30px; height:30px; border-radius:6px; font-size:.9rem;
    text-decoration:none; border:1px solid; cursor:pointer;
    transition:opacity .15s; background:none; padding:0; line-height:1;
}
.act-btn:hover { opacity:.72; }
.act-btn-view   { background:#f0fdf4; color:#15803d; border-color:#86efac; }
.act-btn-prev   { background:#fefce8; color:#854d0e; border-color:#fde047; }
.act-btn-edit   { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.act-btn-del    { background:#f9fafb; color:#9ca3af; border-color:#e5e7eb; }
.act-btn-del:hover { background:#fef2f2; color:#dc2626; border-color:#fca5a5; opacity:1; }

/* ── Lightbox ── */
#idx-lightbox {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.82);
    z-index:9999; align-items:center; justify-content:center; cursor:zoom-out;
}
#idx-lightbox img {
    max-width:90vw; max-height:90vh; border-radius:10px;
    box-shadow:0 8px 40px rgba(0,0,0,.6);
}
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
                <th style="width:64px"></th>
                <th>Título</th>
                <th>Estado</th>
                <th>Publicación</th>
                <th style="width:130px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articulos as $articulo)
            <tr id="row-{{ $articulo->id }}" data-slug="{{ $articulo->slug }}">

                {{-- Miniatura --}}
                <td style="padding:0.5rem 0.5rem 0.5rem 1rem;vertical-align:middle">
                    @if($articulo->imagen_principal)
                    <img src="{{ $articulo->imagen_principal }}"
                         alt="{{ $articulo->image_alt ?? $articulo->titulo }}"
                         class="art-thumb"
                         loading="lazy"
                         onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'art-thumb-empty',innerHTML:'🖼'}))"
                         onclick="abrirLightbox('{{ $articulo->imagen_principal }}')">
                    @else
                    <div class="art-thumb-empty">🖼</div>
                    @endif
                </td>

                {{-- Título --}}
                <td style="vertical-align:middle">
                    <strong>{{ $articulo->titulo }}</strong>
                    @if($articulo->categoriaBlog)
                    <br><small style="color:#9ca3af">{{ $articulo->categoriaBlog->nombre }}</small>
                    @endif
                </td>

                {{-- Estado --}}
                <td style="vertical-align:middle">
                    <select class="estado-sel s-{{ $articulo->estado }}"
                            data-id="{{ $articulo->id }}"
                            data-prev="{{ $articulo->estado }}"
                            onchange="cambiarEstado(this)">
                        <option value="borrador"   {{ $articulo->estado === 'borrador'   ? 'selected' : '' }}>Borrador</option>
                        <option value="programado" {{ $articulo->estado === 'programado' ? 'selected' : '' }}>Programado</option>
                        <option value="publicado"  {{ $articulo->estado === 'publicado'  ? 'selected' : '' }}>Publicado</option>
                        <option value="archivado"  {{ $articulo->estado === 'archivado'  ? 'selected' : '' }}>Archivado</option>
                    </select>
                </td>

                {{-- Fecha --}}
                <td style="vertical-align:middle;font-size:.85rem">
                    @if($articulo->fecha_publicacion)
                    {{ $articulo->fecha_publicacion->format('d/m/Y') }}<br>
                    <span style="color:#9ca3af;font-size:.8rem">{{ $articulo->fecha_publicacion->format('H:i') }}</span>
                    @else
                    <span style="color:#9ca3af">—</span>
                    @endif
                </td>

                {{-- Acciones --}}
                <td style="vertical-align:middle">
                    @php
                        $estaVivo = $articulo->estado === 'publicado'
                            && (!$articulo->fecha_publicacion || $articulo->fecha_publicacion->lte(now()));
                    @endphp
                    <div class="act-btns">
                        @if($articulo->estado === 'publicado')
                        <a href="{{ $estaVivo ? route('blog.show', $articulo->slug) : route('admin.articulos.preview', $articulo) }}"
                           target="_blank"
                           class="act-btn act-btn-view"
                           title="Ver en blog">↗</a>
                        @else
                        <a href="{{ route('admin.articulos.preview', $articulo) }}"
                           target="_blank"
                           class="act-btn act-btn-prev"
                           title="Vista previa">👁</a>
                        @endif

                        <a href="{{ route('admin.articulos.edit', $articulo) }}"
                           class="act-btn act-btn-edit"
                           title="Editar">✏</a>

                        <form method="POST" action="{{ route('admin.articulos.destroy', $articulo) }}"
                              onsubmit="return confirm('¿Eliminar «{{ addslashes($articulo->titulo) }}»?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-btn-del" title="Eliminar">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem">
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

{{-- Lightbox --}}
<div id="idx-lightbox" onclick="cerrarLightbox()">
    <img id="idx-lightbox-img" src="" alt="Vista ampliada">
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

        const row     = document.getElementById(`row-${id}`);
        const btnView = row?.querySelector('.act-btn-view, .act-btn-prev');
        if (btnView) {
            btnView.outerHTML = `<a href="${ESTADO_URL}/${id}/preview" target="_blank" class="act-btn act-btn-prev" title="Vista previa">👁</a>`;
        }
    } catch (e) {
        sel.value     = prev;
        sel.className = `estado-sel s-${prev}`;
        alert('Error al cambiar estado: ' + e.message);
    } finally {
        sel.disabled = false;
    }
}

function abrirLightbox(src) {
    document.getElementById('idx-lightbox-img').src = src;
    document.getElementById('idx-lightbox').style.display = 'flex';
}
function cerrarLightbox() {
    document.getElementById('idx-lightbox').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLightbox(); });
</script>
@endpush
