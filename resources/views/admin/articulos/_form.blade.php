@php $a = $articulo ?? null; @endphp

@push('head')
<style>
.tip {
    display: inline-flex; align-items: center; justify-content: center;
    width: 15px; height: 15px; border-radius: 50%;
    background: #e5e7eb; color: #6b7280;
    font-size: 9px; font-weight: 700; font-style: normal;
    cursor: help; position: relative; margin-left: 5px;
    vertical-align: middle; user-select: none; flex-shrink: 0;
}
.tip::after {
    content: attr(data-tip);
    position: absolute; bottom: calc(100% + 7px); left: 50%;
    transform: translateX(-50%);
    background: #1f2937; color: #f9fafb;
    font-size: .725rem; font-weight: 400; line-height: 1.45;
    padding: .45rem .65rem; border-radius: 6px;
    width: max-content; max-width: 270px; white-space: normal;
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

/* Panel "nueva categoría" */
#nueva-cat-panel {
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 1rem; margin-top: .75rem; display: none;
}
#nueva-cat-panel .form-label { font-size: .8rem; }
#nueva-cat-panel .form-control { font-size: .875rem; }
#nueva-cat-panel small { color: #9ca3af; font-size: .75rem; }
</style>
@endpush

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.5rem">
    <ul style="margin:0;padding-left:1.25rem">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Título y slug --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="titulo">
            Título *
            <span class="tip" tabindex="0" data-tip="Texto principal del artículo. Aparece como titular grande en el artículo destacado y como título de cada tarjeta. Si no rellenas Meta title, Google también usará este texto.">i</span>
            <button type="button" class="btn-regen" data-field="titulo" title="Regenerar con IA">✦</button>
        </label>
        <input type="text" id="titulo" name="titulo" class="form-control"
               value="{{ old('titulo', $a?->titulo) }}" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="slug">
            Slug (URL)
            <span class="tip" tabindex="0" data-tip="Define la URL pública: /blog/tu-slug. Si lo dejas vacío se genera solo desde el título. Una vez publicado, no lo cambies o perderás el posicionamiento en Google.">i</span>
            <small style="color:#9ca3af;margin-left:6px">— vacío = autogenerar</small>
        </label>
        <input type="text" id="slug" name="slug" class="form-control"
               value="{{ old('slug', $a?->slug) }}" placeholder="mi-articulo-sobre-fidelizacion">
    </div>
</div>

{{-- Extracto --}}
<div class="form-group">
    <label class="form-label" for="extracto">
        Extracto
        <span class="tip" tabindex="0" data-tip="Descripción breve visible bajo el título. En el artículo destacado se muestra hasta 180 caracteres; en las tarjetas del grid, hasta 120. Si no rellenas Meta description, Google usará este texto en los resultados de búsqueda.">i</span>
        <button type="button" class="btn-regen" data-field="extracto" title="Regenerar con IA">✦</button>
    </label>
    <textarea id="extracto" name="extracto" class="form-control" rows="2">{{ old('extracto', $a?->extracto) }}</textarea>
</div>

{{-- Contenido --}}
<div class="form-group">
    <label class="form-label" for="contenido">
        Contenido
        <span class="tip" tabindex="0" data-tip="Cuerpo completo del artículo en Markdown: # Título, ## Subtítulo, **negrita**, - listas, [texto](url). El tiempo de lectura que aparece en las tarjetas se calcula automáticamente (200 palabras/min).">i</span>
    </label>
    <textarea id="contenido" name="contenido" class="form-control" rows="16">{{ old('contenido', $a?->contenido) }}</textarea>
</div>

{{-- Imagen y categoría --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="imagen_principal">
            URL imagen principal
            <span class="tip" tabindex="0" data-tip="URL de la foto que aparece en el artículo destacado y en la cabecera de cada tarjeta (200px alto, recortada). Mínimo 800×500px. Puede ser de Unsplash, Pexels o tu propio hosting.">i</span>
        </label>
        <input type="text" id="imagen_principal" name="imagen_principal" class="form-control"
               value="{{ old('imagen_principal', $a?->imagen_principal) }}">
        <div style="margin-top:.4rem">
            <label class="form-label" for="image_alt" style="font-size:.8rem">
                Alt imagen
                <span class="tip" tabindex="0" data-tip="Texto alternativo para lectores de pantalla y Google. Describe la imagen en 10-15 palabras. Mejora el SEO de imágenes.">i</span>
                <button type="button" class="btn-regen" data-field="image_alt" title="Regenerar con IA">✦</button>
            </label>
            <input type="text" id="image_alt" name="image_alt" class="form-control"
                   style="font-size:.85rem"
                   value="{{ old('image_alt', $a?->image_alt) }}" placeholder="Comerciante atendiendo a cliente en tienda local">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="categoria_blog_id">
            Categoría del blog
            <span class="tip" tabindex="0" data-tip="Etiqueta de color sobre la imagen y en las tarjetas (ej. 'CASO DE ÉXITO'). Activa el filtro en la barra superior del blog. Cada categoría tiene su propia página SEO en /blog/categoria/slug.">i</span>
        </label>

        @php
            $catIdActual = old('categoria_blog_id', $a?->categoria_blog_id ?? '');
            $mostrarNueva = old('categoria_blog_id') === 'nueva';
        @endphp

        <select id="categoria_blog_id" name="categoria_blog_id" class="form-control" onchange="toggleNuevaCat(this.value)">
            <option value="">Sin categoría</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}" {{ (string)$catIdActual === (string)$cat->id ? 'selected' : '' }}>
                {{ $cat->nombre }}
            </option>
            @endforeach
            <option value="nueva" {{ $mostrarNueva ? 'selected' : '' }}>— Nueva categoría...</option>
        </select>

        {{-- Panel inline para crear categoría nueva --}}
        <div id="nueva-cat-panel" style="{{ $mostrarNueva ? 'display:block' : 'display:none' }}">
            <div class="form-group" style="margin-bottom:.75rem">
                <label class="form-label" for="categoria_nueva_nombre">Nombre de la categoría *</label>
                <input type="text" id="categoria_nueva_nombre" name="categoria_nueva_nombre"
                       class="form-control" value="{{ old('categoria_nueva_nombre') }}"
                       placeholder="Guías prácticas">
            </div>
            <div class="form-group" style="margin-bottom:.75rem">
                <label class="form-label" for="categoria_nueva_descripcion">
                    Descripción
                    <span class="tip" tabindex="0" data-tip="Texto visible en la página pública de la categoría. Explica qué tipo de artículos agrupa y a quién van dirigidos. Importante para SEO y para que los modelos de IA entiendan el contexto.">i</span>
                </label>
                <textarea id="categoria_nueva_descripcion" name="categoria_nueva_descripcion"
                          class="form-control" rows="2"
                          placeholder="Guías paso a paso para digitalizar y fidelizar a los clientes de tu comercio local.">{{ old('categoria_nueva_descripcion') }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label" for="categoria_nueva_meta_description">Meta description</label>
                <input type="text" id="categoria_nueva_meta_description" name="categoria_nueva_meta_description"
                       class="form-control" value="{{ old('categoria_nueva_meta_description') }}"
                       placeholder="Guías prácticas para el comercio local — Blog Eventify">
                <small>Máx. 160 caracteres. Puedes completarla después desde «Categorías».</small>
            </div>
        </div>

        <div style="margin-top:.4rem">
            <a href="{{ route('admin.categorias.index') }}" target="_blank"
               style="font-size:.78rem;color:#6c3fc5;text-decoration:none">
                Gestionar todas las categorías ↗
            </a>
        </div>
    </div>
</div>

{{-- Etiquetas y autor --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="focus_keyword">
            Keyword principal
            <span class="tip" tabindex="0" data-tip="Palabra clave principal para la que quieres posicionar este artículo. Google la usa para entender el tema. Ej: fidelización clientes bar.">i</span>
            <button type="button" class="btn-regen" data-field="focus_keyword" title="Regenerar con IA">✦</button>
        </label>
        <input type="text" id="focus_keyword" name="focus_keyword" class="form-control"
               value="{{ old('focus_keyword', $a?->focus_keyword) }}" placeholder="fidelización clientes comercio local">
    </div>
    <div class="form-group">
        <label class="form-label" for="etiquetas">
            Etiquetas
            <span class="tip" tabindex="0" data-tip="Palabras clave internas separadas por coma. No se muestran al visitante pero sirven para buscar artículos en el CMS. Ej: fidelización, QR, comercio local">i</span>
            <button type="button" class="btn-regen" data-field="etiquetas" title="Regenerar con IA">✦</button>
            <small style="color:#9ca3af;margin-left:6px">— separadas por coma</small>
        </label>
        <input type="text" id="etiquetas" name="etiquetas" class="form-control"
               value="{{ old('etiquetas', $a?->etiquetas) }}" placeholder="fidelización, comercio local, qr">
    </div>
    <div class="form-group">
        <label class="form-label" for="autor">
            Autor
            <span class="tip" tabindex="0" data-tip="Nombre que aparece en el pie de las tarjetas y en el artículo destacado. Si lo dejas vacío se muestra 'Equipo Eventify'.">i</span>
        </label>
        <input type="text" id="autor" name="autor" class="form-control"
               value="{{ old('autor', $a?->autor) }}" placeholder="Equipo Eventify">
    </div>
</div>

{{-- Estado y fecha --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="estado">
            Estado *
            <span class="tip" tabindex="0" data-tip="Solo los artículos 'Publicado' con fecha ≤ hoy aparecen en el blog. 'Borrador' los guarda sin publicar. 'Archivado' los oculta sin borrarlos.">i</span>
        </label>
        <select id="estado" name="estado" class="form-control">
            @foreach(['borrador' => 'Borrador', 'publicado' => 'Publicado', 'archivado' => 'Archivado'] as $val => $label)
            <option value="{{ $val }}" {{ old('estado', $a?->estado ?? 'borrador') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="fecha_publicacion">
            Fecha de publicación
            <span class="tip" tabindex="0" data-tip="Aparece bajo el autor ('18 abril 2026') y controla el orden: los más recientes primero. Si la fecha es futura el artículo no se mostrará hasta ese momento aunque esté 'Publicado'.">i</span>
        </label>
        <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion" class="form-control"
               value="{{ old('fecha_publicacion', $a?->fecha_publicacion?->format('Y-m-d\TH:i')) }}">
    </div>
</div>

{{-- SEO --}}
<hr style="margin:1.5rem 0;border-color:#f3f4f6">
<h3 style="font-size:1rem;margin-bottom:1rem;color:#6b7280">SEO</h3>

<div class="form-group">
    <label class="form-label" for="meta_title">
        Meta title
        <span class="tip" tabindex="0" data-tip="Texto que Google muestra en los resultados de búsqueda. Máx. 60 caracteres. Si está vacío Google usará el Título.">i</span>
        <button type="button" class="btn-regen" data-field="meta_title" title="Regenerar con IA">✦</button>
    </label>
    <input type="text" id="meta_title" name="meta_title" class="form-control"
           value="{{ old('meta_title', $a?->meta_title) }}">
</div>
<div class="form-group">
    <label class="form-label" for="meta_description">
        Meta description
        <span class="tip" tabindex="0" data-tip="Descripción en los resultados de Google. Idealmente 120-160 caracteres. Si está vacía se usará el Extracto. Influye en el CTR (porcentaje de clics).">i</span>
        <button type="button" class="btn-regen" data-field="meta_description" title="Regenerar con IA">✦</button>
        <small style="color:#9ca3af;margin-left:6px">— máx. 160 caracteres recomendados</small>
    </label>
    <textarea id="meta_description" name="meta_description" class="form-control" rows="2">{{ old('meta_description', $a?->meta_description) }}</textarea>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="canonical">
            URL canónica
            <span class="tip" tabindex="0" data-tip="Solo rellena si el artículo existe también en otra URL (crosspost). Indica a Google cuál es la URL oficial para evitar contenido duplicado.">i</span>
        </label>
        <input type="url" id="canonical" name="canonical" class="form-control"
               value="{{ old('canonical', $a?->canonical) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="og_image">
            OG Image URL
            <span class="tip" tabindex="0" data-tip="Imagen al compartir en WhatsApp, LinkedIn, Twitter. Si está vacía se usa la Imagen principal. Tamaño recomendado: 1200×630px.">i</span>
        </label>
        <input type="text" id="og_image" name="og_image" class="form-control"
               value="{{ old('og_image', $a?->og_image) }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="schema_type">
            Schema type
            <span class="tip" tabindex="0" data-tip="Tipo de contenido para Google (JSON-LD). BlogPosting → artículo estándar. Article → editorial/noticia. HowTo → tutorial paso a paso (Google puede mostrar los pasos en búsquedas sin entrar al artículo).">i</span>
        </label>
        <select id="schema_type" name="schema_type" class="form-control">
            @foreach(['BlogPosting' => 'BlogPosting — artículo de blog', 'Article' => 'Article — editorial/noticia', 'HowTo' => 'HowTo — tutorial paso a paso'] as $val => $label)
            <option value="{{ $val }}" {{ old('schema_type', $a?->schema_type ?? 'BlogPosting') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" style="display:flex;align-items:center;gap:.75rem;padding-top:1.75rem">
        <input type="hidden" name="indexable" value="0">
        <input type="checkbox" id="indexable" name="indexable" value="1"
               {{ old('indexable', $a?->indexable ?? true) ? 'checked' : '' }}>
        <label for="indexable" style="font-weight:600;font-size:.875rem;cursor:pointer;display:flex;align-items:center">
            Indexable por Google
            <span class="tip" tabindex="0" data-tip="Si está marcado, Google puede indexar y mostrar el artículo en búsquedas. Desmárcalo para contenido de prueba o artículos que no quieres en Google.">i</span>
        </label>
    </div>
</div>

{{-- FAQ --}}
<hr style="margin:1.5rem 0;border-color:#f3f4f6">
<h3 style="font-size:1rem;margin-bottom:1rem;color:#6b7280">FAQ
    <span class="tip" tabindex="0" data-tip="Preguntas frecuentes en formato JSON. Google puede mostrarlas directamente en los resultados de búsqueda (schema FAQPage). La IA las genera automáticamente.">i</span>
    <button type="button" class="btn-regen" data-field="faq_json" title="Regenerar FAQ con IA">✦</button>
</h3>
<div class="form-group">
    <textarea id="faq_json" name="faq_json" class="form-control" rows="8"
        placeholder='[{"question": "¿Cómo funciona Eventify?", "answer": "..."}]'
        style="font-family:monospace;font-size:.8rem">{{ old('faq_json', $a?->faq_json ? json_encode($a->faq_json, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea>
    <small style="color:#9ca3af">JSON array de objetos con "question" y "answer". Se renderiza como HTML + schema FAQPage en la vista pública.</small>
</div>

{{-- Metadatos IA (colapsable) --}}
<hr style="margin:1.5rem 0;border-color:#f3f4f6">
<details style="margin-bottom:1rem">
    <summary style="font-size:1rem;color:#6b7280;cursor:pointer;font-weight:600">
        ✦ Metadatos IA
        <span class="tip" tabindex="0" data-tip="Campos usados internamente por el asistente IA. El 'resumen IA' se usa para sugerir enlaces internos en nuevos artículos.">i</span>
    </summary>
    <div style="margin-top:1rem">
        <div class="form-group">
            <label class="form-label" for="ai_context_summary">
                Resumen para IA (contexto editorial)
                <button type="button" class="btn-regen" data-field="ai_context_summary" title="Regenerar con IA">✦</button>
            </label>
            <textarea id="ai_context_summary" name="ai_context_summary" class="form-control" rows="3"
                placeholder="~100 palabras describiendo de qué trata el artículo, para que futuros artículos puedan mencionarlo.">{{ old('ai_context_summary', $a?->ai_context_summary) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="summary_short">
                Resumen breve (para el panel admin)
                <button type="button" class="btn-regen" data-field="summary_short" title="Regenerar con IA">✦</button>
            </label>
            <input type="text" id="summary_short" name="summary_short" class="form-control"
                   value="{{ old('summary_short', $a?->summary_short) }}" placeholder="20-25 palabras resumiendo el artículo">
        </div>
        @if($a?->ai_generated)
        <p style="font-size:.8rem;color:#7c3aed;background:#f5f3ff;padding:.5rem .75rem;border-radius:6px">
            ✦ Generado por IA · {{ $a->ai_last_provider }}/{{ $a->ai_last_model }}
            @if($a->ai_last_generated_at) · {{ $a->ai_last_generated_at->diffForHumans() }} @endif
        </p>
        @endif
    </div>
</details>

@push('scripts')
<script>
function toggleNuevaCat(val) {
    document.getElementById('nueva-cat-panel').style.display = val === 'nueva' ? 'block' : 'none';
    const nombre = document.getElementById('categoria_nueva_nombre');
    nombre.required = val === 'nueva';
}
</script>
@endpush
