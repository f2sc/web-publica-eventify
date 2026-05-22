@php $a = $articulo ?? null; @endphp

@push('head')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
<style>
/* ── Layout dos columnas ── */
.art-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) { .art-layout { grid-template-columns: 1fr; } }

/* ── Barra título ── */
.art-title-bar {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; padding: 1.1rem 1.5rem .9rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.art-title-input {
    width: 100%; border: none; outline: none;
    font-size: 1.45rem; font-weight: 700; color: #111827;
    padding: 0; margin-bottom: .4rem; background: transparent;
    font-family: inherit;
}
.art-title-input::placeholder { color: #d1d5db; font-weight: 400; }
.art-slug-row {
    display: flex; align-items: center;
    gap: .3rem; font-size: .81rem; color: #9ca3af;
}
.art-slug-prefix { flex-shrink: 0; }
.art-slug-input {
    border: none; outline: none; font-size: .81rem;
    color: #6b7280; flex: 1; min-width: 0;
    background: transparent; padding: 0; font-family: inherit;
}
.art-slug-input:focus { color: #111827; }

/* ── Tarjetas columna principal ── */
.art-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; margin-bottom: 1.25rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.art-card-head {
    padding: .55rem 1.25rem;
    background: #f9fafb; border-bottom: 1px solid #f0f0f0;
    border-radius: 10px 10px 0 0;
    font-size: .72rem; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: .07em;
    display: flex; align-items: center; gap: .5rem;
}
.art-card-body { padding: 1.25rem; }
.art-card-body .form-control { border-radius: 6px; }
/* Quill integrado en tarjeta sin bordes extra */
.art-card .ql-toolbar.ql-snow  { border: none; border-bottom: 1px solid #f0f0f0; background: #fafafa; border-radius: 0; }
.art-card .ql-container.ql-snow { border: none; }
.art-card .ql-editor { min-height: 420px; line-height: 1.75; font-size: .95rem; padding: 1.25rem; }
.art-card .ql-editor h1 { font-size: 1.6rem; margin: 1.2rem 0 .6rem; }
.art-card .ql-editor h2 { font-size: 1.3rem; margin: 1rem 0 .5rem; }
.art-card .ql-editor h3 { font-size: 1.1rem; margin: .8rem 0 .4rem; }
.art-card .ql-editor p  { margin: 0 0 .75rem; }
.art-card .ql-editor ul,
.art-card .ql-editor ol  { padding-left: 1.5rem; margin: 0 0 .75rem; }
.art-card .ql-editor blockquote { border-left: 3px solid #c4b5fd; padding-left: 1rem; color: #6b7280; margin: 1rem 0; }
.art-card .ql-editor pre.ql-syntax { background: #1f2937; color: #e5e7eb; border-radius: 6px; padding: 1rem; font-size: .85rem; }

/* ── Sidebar panels ── */
.sbar-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sbar-head {
    padding: .55rem 1rem;
    background: #f9fafb; border-bottom: 1px solid #f0f0f0;
    border-radius: 10px 10px 0 0;
    font-size: .72rem; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .07em;
    display: flex; align-items: center; justify-content: space-between;
    list-style: none;
}
details.sbar-card > summary { list-style: none; cursor: pointer; }
details.sbar-card > summary::-webkit-details-marker { display: none; }
details.sbar-card[open] .sbar-chevron { transform: rotate(180deg); }
.sbar-chevron { transition: transform .2s; flex-shrink: 0; }
.sbar-body { padding: 1rem; }
.sbar-field { margin-bottom: .85rem; }
.sbar-field:last-child { margin-bottom: 0; }
.sbar-field > label {
    display: flex; align-items: center; gap: .3rem;
    font-size: .78rem; font-weight: 600; color: #6b7280; margin-bottom: .3rem;
}
.sbar-field .form-control { font-size: .875rem; padding: .5rem .75rem; }
.sbar-check {
    display: flex; align-items: center; gap: .5rem;
    font-size: .83rem; cursor: pointer; color: #374151; margin-bottom: .85rem;
}
/* Preview imagen destacada */
.feat-img-preview {
    width: 100%; border-radius: 6px; object-fit: cover;
    aspect-ratio: 16/9; display: block;
    background: #f3f4f6; margin-bottom: .75rem;
}
/* Status badge en panel Publicar */
.status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .75rem; font-weight: 600; padding: .2rem .6rem;
    border-radius: 20px;
}
.status-badge.borrador  { background: #fef3c7; color: #92400e; }
.status-badge.publicado { background: #d1fae5; color: #065f46; }
.status-badge.programado { background: #dbeafe; color: #1e40af; }
.status-badge.archivado { background: #f3f4f6; color: #6b7280; }

/* ── Tooltip ── */
.tip {
    display: inline-flex; align-items: center; justify-content: center;
    width: 15px; height: 15px; border-radius: 50%;
    background: #e5e7eb; color: #6b7280;
    font-size: 9px; font-weight: 700; font-style: normal;
    cursor: help; position: relative; margin-left: 4px;
    vertical-align: middle; user-select: none; flex-shrink: 0;
}
.tip::after {
    content: attr(data-tip);
    position: absolute; bottom: calc(100% + 7px); left: 50%;
    transform: translateX(-50%);
    background: #1f2937; color: #f9fafb;
    font-size: .725rem; font-weight: 400; line-height: 1.45;
    padding: .45rem .65rem; border-radius: 6px;
    width: max-content; max-width: 260px; white-space: normal;
    opacity: 0; pointer-events: none; transition: opacity .15s;
    z-index: 200; box-shadow: 0 4px 12px rgba(0,0,0,.2);
}
.tip::before {
    content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent; border-top-color: #1f2937;
    opacity: 0; pointer-events: none; transition: opacity .15s; z-index: 200;
}
.tip:hover::after, .tip:hover::before,
.tip:focus::after, .tip:focus::before { opacity: 1; }

/* ── Panel nueva categoría ── */
#nueva-cat-panel {
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: .85rem; margin-top: .5rem; display: none;
}
#nueva-cat-panel .sbar-field > label { font-size: .76rem; }
#nueva-cat-panel small { color: #9ca3af; font-size: .73rem; }

/* ── Lightbox imagen ── */
#img-lightbox-bg {
    position: fixed; inset: 0; background: rgba(0,0,0,.82);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
#img-lightbox-bg img {
    max-width: 90vw; max-height: 90vh;
    border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.5);
}
/* ── Upload imagen ── */
.img-upload-area {
    border: 1.5px dashed #d1d5db; border-radius: 7px;
    padding: .6rem; text-align: center; cursor: pointer;
    font-size: .78rem; color: #9ca3af; transition: border-color .15s;
    margin-top: .5rem; position: relative; overflow: hidden;
}
.img-upload-area:hover { border-color: #6c3fc5; color: #6c3fc5; }
.img-upload-area input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%;
}

/* ── Botón regenerar IA ── */
.btn-regen {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    background: #ede9fe; color: #7c3aed; border: none;
    font-size: 11px; cursor: pointer; margin-left: 4px;
    vertical-align: middle; transition: background .15s; flex-shrink: 0;
}
.btn-regen:hover { background: #c4b5fd; }
.btn-regen.loading { animation: spin .7s linear infinite; pointer-events: none; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.5rem">
    <ul style="margin:0;padding-left:1.25rem">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ── Barra de título ── --}}
<div class="art-title-bar">
    <input type="text" id="titulo" name="titulo" class="art-title-input"
           placeholder="Escribe el título del artículo..."
           value="{{ old('titulo', $a?->titulo) }}" required>
    <div class="art-slug-row">
        <span class="art-slug-prefix">/blog/</span>
        <input type="text" id="slug" name="slug" class="art-slug-input"
               placeholder="slug-generado-desde-titulo"
               value="{{ old('slug', $a?->slug) }}">
        <span style="flex-shrink:0">— vacío = autogenerar</span>
    </div>
</div>

{{-- ── Grid dos columnas ── --}}
<div class="art-layout">

    {{-- ═══ COLUMNA PRINCIPAL ═══ --}}
    <div class="art-main">

        {{-- Extracto --}}
        <div class="art-card">
            <div class="art-card-head">
                Extracto
                <span class="tip" tabindex="0" data-tip="Descripción breve visible bajo el título. Máx. 180 chars en el destacado, 120 en tarjetas. Si no rellenas Meta description, Google usa esto.">i</span>
                <button type="button" class="btn-regen" data-field="extracto" title="Regenerar con IA">✦</button>
            </div>
            <div class="art-card-body" style="padding-bottom:.85rem">
                <textarea name="extracto" class="form-control" rows="2"
                    style="resize:vertical">{{ old('extracto', $a?->extracto) }}</textarea>
            </div>
        </div>

        {{-- Panel IA --}}
        @include('admin.articulos._ai_panel')

        {{-- Contenido --}}
        <div class="art-card">
            <div class="art-card-head">
                Contenido
                <span id="word-count" style="margin-left:auto;font-weight:500;color:#9ca3af;letter-spacing:normal;text-transform:none;font-size:.72rem">0 palabras</span>
            </div>
            <div id="quill-editor"></div>
            <textarea id="contenido" name="contenido" style="display:none">{{ old('contenido', $a?->contenido) }}</textarea>
        </div>

        {{-- FAQ --}}
        <div class="art-card">
            <div class="art-card-head">
                FAQ
                <span class="tip" tabindex="0" data-tip="Preguntas frecuentes. Google puede mostrarlas en resultados de búsqueda (schema FAQPage). La IA las genera automáticamente.">i</span>
                <button type="button" class="btn-regen" data-field="faq_json" title="Regenerar FAQ con IA">✦</button>
            </div>
            <div class="art-card-body">
                <textarea name="faq_json" class="form-control" rows="7"
                    placeholder='[{"question": "¿Cómo funciona?", "answer": "..."}]'
                    style="font-family:monospace;font-size:.8rem;resize:vertical">{{ old('faq_json', $a?->faq_json ? json_encode($a->faq_json, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea>
                <small style="color:#9ca3af;font-size:.75rem">JSON array — "question" y "answer". Se renderiza con schema FAQPage en el blog.</small>
            </div>
        </div>

    </div>

    {{-- ═══ COLUMNA SIDEBAR ═══ --}}
    <div class="art-sidebar">

        {{-- Panel: Publicar --}}
        <div class="sbar-card">
            <div class="sbar-head">
                Publicar
                @if($a)
                <span class="status-badge {{ $a->estado }}">{{ ucfirst($a->estado) }}</span>
                @endif
            </div>
            <div class="sbar-body">
                <div class="sbar-field">
                    <label for="estado">
                        Estado
                        <span class="tip" tabindex="0" data-tip="Solo 'Publicado' con fecha ≤ hoy aparece en el blog. 'Borrador' lo guarda sin publicar.">i</span>
                    </label>
                    <select id="estado" name="estado" class="form-control">
                        @foreach(['borrador' => 'Borrador', 'programado' => 'Programado', 'publicado' => 'Publicado', 'archivado' => 'Archivado'] as $val => $label)
                        <option value="{{ $val }}" {{ old('estado', $a?->estado ?? 'borrador') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sbar-field">
                    <label for="fecha_publicacion">
                        Fecha de publicación
                        <span class="tip" tabindex="0" data-tip="Controla el orden y visibilidad. Si la fecha es futura, el artículo no aparece aunque esté 'Publicado'.">i</span>
                    </label>
                    <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion"
                           class="form-control"
                           value="{{ old('fecha_publicacion', $a?->fecha_publicacion?->format('Y-m-d\TH:i')) }}">
                </div>
                <label class="sbar-check">
                    <input type="hidden" name="enviar_newsletter" value="0">
                    <input type="checkbox" name="enviar_newsletter" value="1"
                           {{ old('enviar_newsletter', $a?->enviar_newsletter ?? true) ? 'checked' : '' }}>
                    <span>Enviar newsletter al publicar
                        <span class="tip" tabindex="0" data-tip="Notifica a los suscriptores confirmados cuando el artículo se publique.">i</span>
                    </span>
                </label>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.25rem">
                    {{ $a ? 'Guardar cambios' : 'Guardar artículo' }}
                </button>
            </div>
        </div>

        {{-- Panel: Imagen destacada --}}
        <div class="sbar-card">
            <div class="sbar-head">Imagen destacada</div>
            <div class="sbar-body">
                {{-- Preview con click para ampliar --}}
                <div id="feat-img-wrap" style="{{ $a?->imagen_principal ? '' : 'display:none' }}">
                    <img id="feat-img-preview" src="{{ $a?->imagen_principal }}" alt="{{ $a?->image_alt }}"
                         class="feat-img-preview"
                         style="cursor:zoom-in"
                         onclick="abrirLightbox(this.src)"
                         onerror="document.getElementById('feat-img-wrap').style.display='none'">
                </div>
                <div class="sbar-field">
                    <label for="imagen_principal">URL de imagen</label>
                    <input type="text" id="imagen_principal" name="imagen_principal"
                           class="form-control"
                           placeholder="https://..."
                           value="{{ old('imagen_principal', $a?->imagen_principal) }}"
                           oninput="actualizarPreviewImagen(this.value)">
                </div>
                {{-- Subir imagen --}}
                <div class="img-upload-area" id="img-upload-area">
                    ↑ Subir imagen desde tu equipo
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                           onchange="subirImagen(this)">
                </div>
                <div id="img-upload-status" style="font-size:.75rem;color:#6b7280;margin-top:.35rem;display:none"></div>
                <div class="sbar-field" style="margin-top:.75rem">
                    <label for="image_alt">
                        Texto alt
                        <span class="tip" tabindex="0" data-tip="Describe la imagen para lectores de pantalla y SEO. 10-15 palabras.">i</span>
                        <button type="button" class="btn-regen" data-field="image_alt" title="Regenerar con IA">✦</button>
                    </label>
                    <input type="text" id="image_alt" name="image_alt"
                           class="form-control"
                           placeholder="Descripción de la imagen..."
                           value="{{ old('image_alt', $a?->image_alt) }}">
                </div>
                @if($a)
                @php $aiSettings = \App\Services\AI\AiSettingsService::get(); @endphp
                <div id="img-ai-status" style="font-size:.78rem;color:#7c3aed;display:none;align-items:center;gap:.35rem;margin-top:.35rem">
                    <span style="animation:spin 1s linear infinite;display:inline-block">⟳</span>
                    <span id="img-ai-status-text">Generando imagen...</span>
                </div>
                <div style="display:flex;gap:.4rem;margin-top:.5rem;align-items:stretch">
                    <button type="button" id="ai-image-btn"
                        style="flex:1;justify-content:center;background:#fff;color:#6c3fc5;border:1.5px solid #c4b5fd;border-radius:7px;padding:.45rem .75rem;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem">
                        🖼 Generar imagen con IA
                        <small style="color:#9ca3af;font-weight:400">(coste extra)</small>
                    </button>
                    <button type="button" id="ai-image-copy-btn" title="Copiar prompt al portapapeles para generarlo en ChatGPT"
                        style="flex-shrink:0;background:#fff;color:#7c3aed;border:1.5px solid #c4b5fd;border-radius:7px;padding:.45rem .6rem;font-size:.88rem;cursor:pointer;display:flex;align-items:center">
                        📋
                    </button>
                </div>
                <script>
                const AI_IMG_STYLE = @json($aiSettings->prompt_image ?? '');
                </script>
                @endif
            </div>
        </div>

        {{-- Lightbox --}}
        <div id="img-lightbox-bg" style="display:none" onclick="cerrarLightbox()">
            <img id="img-lightbox-img" src="" alt="Vista ampliada">
        </div>

        {{-- Panel: Categoría --}}
        <div class="sbar-card">
            <div class="sbar-head">Categoría</div>
            <div class="sbar-body">
                @php
                    $catIdActual  = old('categoria_blog_id', $a?->categoria_blog_id ?? '');
                    $mostrarNueva = old('categoria_blog_id') === 'nueva';
                @endphp
                <div class="sbar-field" style="margin-bottom:0">
                    <label for="categoria_blog_id">
                        <span class="tip" tabindex="0" data-tip="Etiqueta sobre la imagen de cada tarjeta. Cada categoría tiene su propia página SEO en /blog/categoria/slug.">i</span>
                        Categoría del blog
                    </label>
                    <select id="categoria_blog_id" name="categoria_blog_id"
                            class="form-control" onchange="toggleNuevaCat(this.value)">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$catIdActual === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                        @endforeach
                        <option value="nueva" {{ $mostrarNueva ? 'selected' : '' }}>— Nueva categoría...</option>
                    </select>
                </div>
                <div id="nueva-cat-panel" style="{{ $mostrarNueva ? 'display:block' : 'display:none' }}">
                    <div class="sbar-field">
                        <label for="categoria_nueva_nombre">Nombre *</label>
                        <input type="text" id="categoria_nueva_nombre" name="categoria_nueva_nombre"
                               class="form-control" value="{{ old('categoria_nueva_nombre') }}"
                               placeholder="Guías prácticas">
                    </div>
                    <div class="sbar-field">
                        <label for="categoria_nueva_descripcion">Descripción</label>
                        <textarea id="categoria_nueva_descripcion" name="categoria_nueva_descripcion"
                                  class="form-control" rows="2"
                                  placeholder="Breve descripción de la categoría...">{{ old('categoria_nueva_descripcion') }}</textarea>
                    </div>
                    <div class="sbar-field">
                        <label for="categoria_nueva_meta_description">Meta description</label>
                        <input type="text" id="categoria_nueva_meta_description" name="categoria_nueva_meta_description"
                               class="form-control" value="{{ old('categoria_nueva_meta_description') }}"
                               placeholder="Categoría — Blog Eventify">
                        <small>Máx. 160 chars. Puedes completarla después.</small>
                    </div>
                </div>
                <div style="margin-top:.5rem">
                    <a href="{{ route('admin.categorias.index') }}" target="_blank"
                       style="font-size:.76rem;color:#6c3fc5;text-decoration:none">
                        Gestionar categorías ↗
                    </a>
                </div>
            </div>
        </div>

        {{-- Panel: Autor --}}
        <div class="sbar-card">
            <div class="sbar-head">Autor</div>
            <div class="sbar-body">
                <div class="sbar-field" style="margin-bottom:0">
                    <label for="autor">
                        <span class="tip" tabindex="0" data-tip="Aparece en las tarjetas y en el artículo destacado. Vacío = 'Equipo Eventify'.">i</span>
                        Nombre del autor
                    </label>
                    <input type="text" id="autor" name="autor" class="form-control"
                           placeholder="Equipo Eventify"
                           value="{{ old('autor', $a?->autor) }}">
                </div>
            </div>
        </div>

        {{-- Panel: Serie (colapsable) --}}
        <details class="sbar-card" {{ old('serie_id', $a?->serie_id) ? 'open' : '' }}>
            <summary class="sbar-head">
                Serie
                <svg class="sbar-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </summary>
            <div class="sbar-body">
                <div class="sbar-field">
                    <label for="serie_id">
                        <span class="tip" tabindex="0" data-tip="Si el artículo pertenece a una serie, aparecerá navegación anterior/siguiente en el blog.">i</span>
                        Serie
                    </label>
                    <select id="serie_id" name="serie_id" class="form-control" onchange="toggleOrden(this.value)">
                        <option value="">Sin serie</option>
                        @foreach($series ?? [] as $serie)
                        <option value="{{ $serie->id }}" {{ old('serie_id', $a?->serie_id) == $serie->id ? 'selected' : '' }}>
                            {{ $serie->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="sbar-field" id="orden-group" style="{{ old('serie_id', $a?->serie_id) ? '' : 'display:none' }}">
                    <label for="orden_en_serie">Orden en la serie</label>
                    <input type="number" id="orden_en_serie" name="orden_en_serie"
                           class="form-control" min="1" placeholder="1"
                           value="{{ old('orden_en_serie', $a?->orden_en_serie) }}">
                </div>
            </div>
        </details>

        {{-- Panel: SEO (colapsable) --}}
        <details class="sbar-card">
            <summary class="sbar-head">
                SEO
                <svg class="sbar-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </summary>
            <div class="sbar-body">
                <div class="sbar-field">
                    <label for="focus_keyword">
                        Keyword principal
                        <button type="button" class="btn-regen" data-field="focus_keyword" title="Regenerar con IA">✦</button>
                    </label>
                    <input type="text" id="focus_keyword" name="focus_keyword"
                           class="form-control" placeholder="fidelización clientes bar"
                           value="{{ old('focus_keyword', $a?->focus_keyword) }}">
                </div>
                <div class="sbar-field">
                    <label for="etiquetas">
                        Etiquetas
                        <button type="button" class="btn-regen" data-field="etiquetas" title="Regenerar con IA">✦</button>
                        <span class="tip" tabindex="0" data-tip="Separadas por coma. No visibles al visitante, útiles para buscar en el CMS.">i</span>
                    </label>
                    <input type="text" id="etiquetas" name="etiquetas"
                           class="form-control" placeholder="tag1, tag2, tag3"
                           value="{{ old('etiquetas', $a?->etiquetas) }}">
                </div>
                <div class="sbar-field">
                    <label for="meta_title">
                        Meta title
                        <button type="button" class="btn-regen" data-field="meta_title" title="Regenerar con IA">✦</button>
                        <span class="tip" tabindex="0" data-tip="Texto en los resultados de Google. Máx. 60 chars. Vacío = usa el Título.">i</span>
                    </label>
                    <input type="text" id="meta_title" name="meta_title"
                           class="form-control"
                           value="{{ old('meta_title', $a?->meta_title) }}">
                </div>
                <div class="sbar-field">
                    <label for="meta_description">
                        Meta description
                        <button type="button" class="btn-regen" data-field="meta_description" title="Regenerar con IA">✦</button>
                        <span class="tip" tabindex="0" data-tip="120-160 chars. Vacía = usa el Extracto.">i</span>
                    </label>
                    <textarea id="meta_description" name="meta_description"
                              class="form-control" rows="2"
                              style="resize:vertical">{{ old('meta_description', $a?->meta_description) }}</textarea>
                </div>
                <div class="sbar-field">
                    <label for="schema_type">
                        Schema type
                        <span class="tip" tabindex="0" data-tip="Tipo de contenido para Google (JSON-LD). HowTo permite mostrar pasos directamente en búsquedas.">i</span>
                    </label>
                    <select id="schema_type" name="schema_type" class="form-control">
                        @foreach(['BlogPosting' => 'BlogPosting', 'Article' => 'Article', 'HowTo' => 'HowTo'] as $val => $label)
                        <option value="{{ $val }}" {{ old('schema_type', $a?->schema_type ?? 'BlogPosting') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sbar-field">
                    <label for="canonical">URL canónica
                        <span class="tip" tabindex="0" data-tip="Solo si el artículo existe también en otra URL (crosspost).">i</span>
                    </label>
                    <input type="url" id="canonical" name="canonical"
                           class="form-control"
                           value="{{ old('canonical', $a?->canonical) }}">
                </div>
                <div class="sbar-field">
                    <label for="og_image">OG Image URL
                        <span class="tip" tabindex="0" data-tip="Imagen al compartir en redes. Vacía = usa imagen principal. 1200×630px recomendado.">i</span>
                    </label>
                    <input type="text" id="og_image" name="og_image"
                           class="form-control"
                           value="{{ old('og_image', $a?->og_image) }}">
                </div>
                <label class="sbar-check" style="margin-bottom:0">
                    <input type="hidden" name="indexable" value="0">
                    <input type="checkbox" id="indexable" name="indexable" value="1"
                           {{ old('indexable', $a?->indexable ?? true) ? 'checked' : '' }}>
                    <span>Indexable por Google
                        <span class="tip" tabindex="0" data-tip="Desmarca para contenido de prueba o artículos que no quieres en Google.">i</span>
                    </span>
                </label>
            </div>
        </details>

        {{-- Panel: Metadatos IA (colapsable) --}}
        <details class="sbar-card">
            <summary class="sbar-head">
                ✦ Metadatos IA
                <svg class="sbar-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </summary>
            <div class="sbar-body">
                <div class="sbar-field">
                    <label for="ai_context_summary">
                        Resumen para IA
                        <button type="button" class="btn-regen" data-field="ai_context_summary" title="Regenerar con IA">✦</button>
                        <span class="tip" tabindex="0" data-tip="~100 palabras describiendo el artículo. Lo usa la IA para sugerir enlaces en artículos futuros.">i</span>
                    </label>
                    <textarea id="ai_context_summary" name="ai_context_summary"
                              class="form-control" rows="3"
                              style="resize:vertical"
                              placeholder="Resumen interno de 80-100 palabras...">{{ old('ai_context_summary', $a?->ai_context_summary) }}</textarea>
                </div>
                <div class="sbar-field">
                    <label for="summary_short">
                        Resumen breve
                        <button type="button" class="btn-regen" data-field="summary_short" title="Regenerar con IA">✦</button>
                    </label>
                    <input type="text" id="summary_short" name="summary_short"
                           class="form-control" placeholder="20-25 palabras"
                           value="{{ old('summary_short', $a?->summary_short) }}">
                </div>
                @if($a?->ai_generated)
                <p style="font-size:.75rem;color:#7c3aed;background:#f5f3ff;padding:.4rem .6rem;border-radius:6px;margin:0">
                    ✦ {{ $a->ai_last_provider }}/{{ $a->ai_last_model }}
                    @if($a->ai_last_generated_at) · {{ $a->ai_last_generated_at->diffForHumans() }} @endif
                </p>
                @endif
            </div>
        </details>

    </div>{{-- /sidebar --}}

</div>{{-- /art-layout --}}

@push('scripts')
<script>
function toggleNuevaCat(val) {
    document.getElementById('nueva-cat-panel').style.display = val === 'nueva' ? 'block' : 'none';
    const nombre = document.getElementById('categoria_nueva_nombre');
    if (nombre) nombre.required = val === 'nueva';
}
function toggleOrden(serieId) {
    document.getElementById('orden-group').style.display = serieId ? 'block' : 'none';
}

// ——— Lightbox imagen ———
function abrirLightbox(src) {
    document.getElementById('img-lightbox-img').src = src;
    document.getElementById('img-lightbox-bg').style.display = 'flex';
}
function cerrarLightbox() {
    document.getElementById('img-lightbox-bg').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLightbox(); });

// ——— Preview en tiempo real ———
function actualizarPreviewImagen(url) {
    const wrap = document.getElementById('feat-img-wrap');
    const img  = document.getElementById('feat-img-preview');
    if (url) { img.src = url; wrap.style.display = 'block'; }
    else      { wrap.style.display = 'none'; }
}

// ——— Subida de imagen ———
const UPLOAD_URL = '{{ route("admin.upload-imagen") }}';
async function subirImagen(input) {
    if (!input.files?.length) return;
    const status = document.getElementById('img-upload-status');
    const area   = document.getElementById('img-upload-area');
    status.textContent = '⟳ Subiendo...';
    status.style.display = 'block';
    area.style.opacity = '0.6';

    const fd = new FormData();
    fd.append('image', input.files[0]);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    if (typeof AI_ARTICLE_ID !== 'undefined' && AI_ARTICLE_ID) fd.append('article_id', AI_ARTICLE_ID);
    const slugField = document.querySelector('[name="slug"]');
    if (slugField?.value) fd.append('article_slug', slugField.value);

    try {
        const r   = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        const res = await r.json();
        if (res.ok) {
            document.getElementById('imagen_principal').value = res.url;
            actualizarPreviewImagen(res.url);
            status.textContent = '✓ Imagen subida correctamente';
            status.style.color = '#065f46';
        } else {
            status.textContent = '✗ Error: ' + (res.message || 'desconocido');
            status.style.color = '#dc2626';
        }
    } catch (e) {
        status.textContent = '✗ Error de red: ' + e.message;
        status.style.color = '#dc2626';
    } finally {
        area.style.opacity = '1';
        input.value = '';
    }
}

// ——— Editor Quill ———
(function () {
    const textarea = document.getElementById('contenido');

    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ header: [1, 2, 3, false] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ color: [] }, { background: [] }],
                [{ font: [] }],
                [{ size: ['small', false, 'large', 'huge'] }],
                ['link', 'image'],
                ['clean'],
            ],
        },
    });

    window.quillEditor = quill;

    // ── Contador de palabras en tiempo real ──
    function actualizarContadorPalabras() {
        const texto  = quill.getText().trim();
        const count  = texto.length ? texto.split(/\s+/).filter(Boolean).length : 0;
        const el = document.getElementById('word-count');
        if (el) el.textContent = count.toLocaleString('es-ES') + ' palabras';
    }
    quill.on('text-change', actualizarContadorPalabras);

    const initial = textarea.value.trim();
    if (initial) {
        const isHtml = initial.startsWith('<');
        quill.clipboard.dangerouslyPasteHTML(
            isHtml ? initial : (typeof marked !== 'undefined' ? marked.parse(initial) : initial)
        );
        actualizarContadorPalabras();
    }

    textarea.closest('form').addEventListener('submit', function () {
        textarea.value = quill.root.innerHTML;
    });
})();
</script>
@endpush
