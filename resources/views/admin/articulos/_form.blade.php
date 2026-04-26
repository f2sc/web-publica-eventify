@php $a = $articulo ?? null; @endphp

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.5rem">
    <ul style="margin:0;padding-left:1.25rem">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Título y slug --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" class="form-control"
               value="{{ old('titulo', $a?->titulo) }}" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="slug">Slug (URL) <small style="color:#9ca3af">— dejar vacío para autogenerar</small></label>
        <input type="text" id="slug" name="slug" class="form-control"
               value="{{ old('slug', $a?->slug) }}" placeholder="mi-articulo-sobre-fidelizacion">
    </div>
</div>

{{-- Extracto --}}
<div class="form-group">
    <label class="form-label" for="extracto">Extracto</label>
    <textarea id="extracto" name="extracto" class="form-control" rows="2">{{ old('extracto', $a?->extracto) }}</textarea>
</div>

{{-- Contenido --}}
<div class="form-group">
    <label class="form-label" for="contenido">Contenido</label>
    <textarea id="contenido" name="contenido" class="form-control" rows="16">{{ old('contenido', $a?->contenido) }}</textarea>
</div>

{{-- Imagen y categoría --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="imagen_principal">URL imagen principal</label>
        <input type="text" id="imagen_principal" name="imagen_principal" class="form-control"
               value="{{ old('imagen_principal', $a?->imagen_principal) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="categoria_blog">Categoría del blog</label>
        <input type="text" id="categoria_blog" name="categoria_blog" class="form-control"
               value="{{ old('categoria_blog', $a?->categoria_blog) }}">
    </div>
</div>

{{-- Etiquetas y autor --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="etiquetas">Etiquetas <small style="color:#9ca3af">— separadas por coma</small></label>
        <input type="text" id="etiquetas" name="etiquetas" class="form-control"
               value="{{ old('etiquetas', $a?->etiquetas) }}" placeholder="fidelización, comercio local, qr">
    </div>
    <div class="form-group">
        <label class="form-label" for="autor">Autor</label>
        <input type="text" id="autor" name="autor" class="form-control"
               value="{{ old('autor', $a?->autor) }}" placeholder="Equipo Eventify">
    </div>
</div>

{{-- Estado y fecha --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="estado">Estado *</label>
        <select id="estado" name="estado" class="form-control">
            @foreach(['borrador' => 'Borrador', 'publicado' => 'Publicado', 'archivado' => 'Archivado'] as $val => $label)
            <option value="{{ $val }}" {{ old('estado', $a?->estado ?? 'borrador') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="fecha_publicacion">Fecha de publicación</label>
        <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion" class="form-control"
               value="{{ old('fecha_publicacion', $a?->fecha_publicacion?->format('Y-m-d\TH:i')) }}">
    </div>
</div>

{{-- SEO --}}
<hr style="margin:1.5rem 0;border-color:#f3f4f6">
<h3 style="font-size:1rem;margin-bottom:1rem;color:#6b7280">SEO</h3>

<div class="form-group">
    <label class="form-label" for="meta_title">Meta title</label>
    <input type="text" id="meta_title" name="meta_title" class="form-control"
           value="{{ old('meta_title', $a?->meta_title) }}">
</div>
<div class="form-group">
    <label class="form-label" for="meta_description">Meta description <small style="color:#9ca3af">— máx. 320 caracteres</small></label>
    <textarea id="meta_description" name="meta_description" class="form-control" rows="2">{{ old('meta_description', $a?->meta_description) }}</textarea>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="canonical">URL canónica</label>
        <input type="url" id="canonical" name="canonical" class="form-control"
               value="{{ old('canonical', $a?->canonical) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="og_image">OG Image URL</label>
        <input type="text" id="og_image" name="og_image" class="form-control"
               value="{{ old('og_image', $a?->og_image) }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="schema_type">Schema type</label>
        <select id="schema_type" name="schema_type" class="form-control">
            @foreach(['BlogPosting' => 'BlogPosting', 'Article' => 'Article', 'HowTo' => 'HowTo'] as $val => $label)
            <option value="{{ $val }}" {{ old('schema_type', $a?->schema_type ?? 'BlogPosting') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:1.75rem">
        <input type="hidden" name="indexable" value="0">
        <input type="checkbox" id="indexable" name="indexable" value="1"
               {{ old('indexable', $a?->indexable ?? true) ? 'checked' : '' }}>
        <label for="indexable" style="font-weight:600;font-size:0.875rem;cursor:pointer">Indexable por Google</label>
    </div>
</div>
