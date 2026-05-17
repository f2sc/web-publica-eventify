@php $c = $categoria ?? null; @endphp

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.5rem">
    <ul style="margin:0;padding-left:1.25rem">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="nombre">Nombre *</label>
        <input type="text" id="nombre" name="nombre" class="form-control"
               value="{{ old('nombre', $c?->nombre) }}" required
               placeholder="Casos de éxito">
        <small style="color:#9ca3af;font-size:.8rem">Aparece en las tarjetas y en la página de categoría.</small>
    </div>
    <div class="form-group">
        <label class="form-label" for="slug">Slug (URL) <small style="color:#9ca3af">— dejar vacío para autogenerar</small></label>
        <input type="text" id="slug" name="slug" class="form-control"
               value="{{ old('slug', $c?->slug) }}"
               placeholder="casos-de-exito">
        <small style="color:#9ca3af;font-size:.8rem">URL: <code>/blog/categoria/tu-slug</code></small>
    </div>
</div>

<div class="form-group">
    <label class="form-label" for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" class="form-control" rows="4"
              placeholder="Explica qué tipo de contenido agrupa esta categoría. Este texto aparece en la página de categoría y ayuda a Google y a los modelos de IA a entender el contexto.">{{ old('descripcion', $c?->descripcion) }}</textarea>
    <small style="color:#9ca3af;font-size:.8rem">Visible en la página pública de categoría. Clave para SEO e IA: describe qué tipo de artículos agrupa y a quién van dirigidos.</small>
</div>

<hr style="margin:1.5rem 0;border-color:#f3f4f6">
<h3 style="font-size:1rem;margin-bottom:1rem;color:#6b7280">SEO</h3>

<div class="form-group">
    <label class="form-label" for="meta_title">Meta title</label>
    <input type="text" id="meta_title" name="meta_title" class="form-control"
           value="{{ old('meta_title', $c?->meta_title) }}"
           placeholder="Casos de éxito — Blog Eventify">
    <small style="color:#9ca3af;font-size:.8rem">Máx. 60 caracteres. Si está vacío: «{Nombre de categoría} — Blog Eventify».</small>
</div>
<div class="form-group">
    <label class="form-label" for="meta_description">Meta description</label>
    <textarea id="meta_description" name="meta_description" class="form-control" rows="2"
              placeholder="Historias reales de comercios locales que han multiplicado su base de clientes con Eventify. Inspírate con sus resultados.">{{ old('meta_description', $c?->meta_description) }}</textarea>
    <small style="color:#9ca3af;font-size:.8rem">Máx. 160 caracteres. Aparece en Google y al compartir. Si está vacío se usa la descripción.</small>
</div>
<div class="form-group">
    <label class="form-label" for="og_image">OG Image URL</label>
    <input type="text" id="og_image" name="og_image" class="form-control"
           value="{{ old('og_image', $c?->og_image) }}"
           placeholder="https://...">
    <small style="color:#9ca3af;font-size:.8rem">Imagen al compartir la categoría en redes. 1200×630px recomendado.</small>
</div>
