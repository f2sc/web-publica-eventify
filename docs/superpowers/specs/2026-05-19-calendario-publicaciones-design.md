# Diseño: Calendario de Publicaciones con Series e IA

**Fecha:** 2026-05-19  
**Estado:** Aprobado para implementación  
**Proyecto:** web-publica-eventify (CMS Eventify Admin)

---

## Objetivo

Añadir un módulo editorial al CMS que permita:

1. Planificar contenido mediante un panel IA que genera ideas de artículos sueltos o planes de series enlazadas.
2. Gestionar un "tintero" de artículos pendientes de programar.
3. Visualizar y programar publicaciones en un calendario mensual con autopublicación por fecha y hora.
4. Navegar las series desde el blog público con página propia y navegación anterior/siguiente.
5. Enviar newsletter automáticamente al publicar si el artículo lo tiene activado.

---

## 1. Modelo de datos

### 1.1 Tabla nueva: `series`

```
id                  unsignedBigInteger PK
nombre              string(255)
slug                string(255) unique
descripcion         text nullable
categoria_blog_id   FK → categoria_blogs.id nullable
timestamps
```

### 1.2 Cambios en `articulos`

```
+ serie_id          FK → series.id nullable (con onDelete SET NULL)
+ orden_en_serie    unsignedSmallInteger nullable
+ enviar_newsletter boolean default true
  estado            enum: añade 'programado' al enum existente
                    valores finales: borrador | programado | publicado | archivado
  fecha_publicacion datetime (ya existe — sin cambio de tipo)
```

El campo `fecha_publicacion` ya es `datetime` en BD y en el cast del modelo. El cambio es solo en el formulario: `type="date"` → `type="datetime-local"`, y en la validación: `'date'` → `'date_format:Y-m-d\TH:i'`.

### 1.3 Modelo `Serie`

```php
// app/Models/Serie.php
fillable: nombre, slug, descripcion, categoria_blog_id
casts: —
relations:
  categoriaBlog(): BelongsTo → CategoriaBlog
  articulos(): HasMany → Articulo (orden_en_serie asc)
```

### 1.4 Cambios en modelo `Articulo`

```php
// Nuevos fillable
'serie_id', 'orden_en_serie', 'enviar_newsletter'

// Nueva relación
serie(): BelongsTo → Serie

// El scope scopePublicados no cambia — solo filtra estado=publicado
// La autopublicación la hace el cron (ver sección 6)
```

---

## 2. Flujo de estados de un artículo

```
borrador
  ├─ sin fecha: está en el tintero, sin plan
  └─ con fecha: está en el calendario como borrador (amarillo)

programado
  └─ tiene contenido + fecha_publicacion asignada
     El cron lo publica cuando fecha_publicacion <= now()

publicado
  └─ visible en el blog (scopePublicados lo filtra)

archivado
  └─ retirado del blog, no editable desde el calendario
```

**Regla de autopublicación:** un artículo solo puede pasar a `publicado` automáticamente si estaba en `programado`. Un `borrador` con fecha nunca se autopublica — requiere intervención manual.

**Indicador visual en el calendario:**
- `borrador` con fecha → amarillo / dashed
- `programado` con contenido → azul / dashed verde (listo)
- `programado` sin contenido (`contenido IS NULL OR contenido = ''`) → azul parpadeante rojo con ⚠
- `publicado` → verde sólido

---

## 3. Módulo Calendario — Backend

### 3.1 Rutas nuevas

```php
// Grupo: middleware CmsAuth, prefix admin/calendario

GET  /admin/calendario              CalendarioController@index
GET  /admin/calendario/events       CalendarioController@events   // JSON
POST /admin/calendario/programar    CalendarioController@programarSerie
```

```php
// Rutas de Series (prefix admin/series)

GET    /admin/series                SerieController@index
POST   /admin/series                SerieController@store
PUT    /admin/series/{serie}        SerieController@update
DELETE /admin/series/{serie}        SerieController@destroy
```

### 3.2 `CalendarioController`

**`index()`**
- Carga todas las series con sus artículos para el tintero (artículos sin `fecha_publicacion` o con `estado = borrador`).
- Pasa a la vista: `$series`, `$sueltos` (artículos sin serie y sin fecha), `$avisos` (artículos `estado = programado OR borrador` con `fecha_publicacion` entre hoy y +7 días y sin contenido).
- El calendario en sí se rellena vía JS llamando a `events`.

**`events(Request $request)`**
- Recibe `year` y `month` por query string.
- Devuelve JSON con todos los artículos que tienen `fecha_publicacion` en ese mes, incluyendo: `id`, `titulo`, `estado`, `fecha_publicacion` (con hora), `serie_id`, `contenido_vacio` (bool).
- Usado por el JS para navegar entre meses sin recargar.

**`programarSerie(Request $request)`**
- Recibe: `serie_id`, `start_datetime` (Y-m-d H:i), `cadencia` (xdias|semana|xsemanas), parámetros de cadencia.
- Calcula las fechas para cada artículo de la serie (por `orden_en_serie`).
- Actualiza `fecha_publicacion` en cada artículo de la serie.
- No cambia el `estado` — siguen siendo `borrador`.
- Devuelve JSON con las fechas asignadas.

### 3.3 `SerieController`

CRUD estándar. `store` y `update` validan: `nombre` requerido, `slug` único auto-generado si vacío, `categoria_blog_id` nullable.

---

## 4. Módulo Calendario — Frontend

### 4.1 Vista `admin/calendario/index.blade.php`

Layout de dos columnas fijas (`310px` sidebar + `1fr` calendario), sin scroll de página — cada zona tiene su propio scroll interno.

**Sidebar izquierdo — Panel IA:**

Toggle "Artículo único" / "Serie de artículos":

- **Artículo único:** campo de categoría + textarea grande de descripción → botón "✦ Generar ideas de títulos" → lista de títulos sugeridos con botón `+` para añadir al tintero.
- **Serie:** campos categoría, nº artículos, nombre de serie, textarea descripción/audiencia/objetivo → botón "✦ Generar plan de serie" → plan con N artículos (título + descripción de enlazado) → botones "Añadir al tintero" y "📅 Programar en calendario".

**Sidebar izquierdo — Tintero:**

Lista de artículos **sin `fecha_publicacion` asignada** con `estado IN (borrador, programado)`. Un artículo que ya tiene fecha desaparece del tintero y aparece solo en el calendario. Si se borra la fecha desde el editor, vuelve al tintero.
- Series agrupadas en acordeón colapsable, con orden visible (1, 2, 3...) y badge de estado.
- Artículos sueltos al final, separados por label.
- Hint de arrastre: "↔ Arrastra al calendario para programar".

**Área derecha — Calendario:**

- Barra de avisos naranja fija en la parte superior: lista artículos `programado` o `borrador` con `fecha_publicacion` en los próximos 7 días y sin contenido. Cada item clicable → redirige a `/admin/articulos/{id}/edit`.
- Toolbar: navegación de mes (‹ ›), botón Hoy, toggle Mes / Semana / Lista.
- Los eventos muestran: punto de color de serie (si aplica), título truncado, hora a la derecha.
- Eventos sin contenido: animación CSS `pulse` rojo suave + icono ⚠.
- Clic en evento → popover con: título, fecha + hora, estado, acciones (Editar, Generar contenido con IA si tiene ⚠, Cambiar fecha, Ver en blog si publicado).

**Modal "Programar en calendario":**

- Fecha del primer artículo: `input[type=date]`.
- Hora de publicación: `input[type=time]` (se aplica a todos los artículos de la serie).
- Cadencia: toggle "Cada X días" / "Días de la semana" / "Cada X semanas" con sub-campos interactivos.
- Preview en tiempo real: lista de artículos con fecha+hora calculada y badge "sin contenido".
- Aviso: "Los artículos se crearán como borradores. Debes generar el contenido y cambiar el estado a 'programado' antes de la fecha."
- Confirmar → llama a `POST /admin/calendario/programar`.

### 4.2 JavaScript del calendario

- Carga inicial: renderiza el mes actual con los eventos que devuelve `events?year=X&month=Y`.
- Cambio de mes: nueva llamada AJAX a `events`, re-renderiza el grid.
- Modal: lógica de cadencia + preview de fechas en tiempo real (vanilla JS, sin dependencias).
- Los eventos se renderizan en el grid por `fecha_publicacion.date`.

---

## 5. Cambios en artículos existentes

### 5.1 Formulario `_form.blade.php`

Nuevos campos:
- **Serie:** `<select>` con todas las series (+ opción "Ninguna"). Al seleccionar una serie aparece el campo de orden.
- **Orden en la serie:** `<input type="number">`, visible solo si hay serie seleccionada.
- **Hora de publicación:** el campo `fecha_publicacion` pasa a `type="datetime-local"`.
- **Estado:** añadir la opción `programado` al `<select>` de estado.
- **Enviar newsletter al publicar:** `<input type="checkbox">` checked por defecto.

### 5.2 `ArticuloController`

- `validar()`: añadir `serie_id`, `orden_en_serie`, `enviar_newsletter`; cambiar regla de `fecha_publicacion` a `'date_format:Y-m-d\TH:i'`.
- `resolverCategoria()`: sin cambios.

---

## 6. Cron de autopublicación

**Archivo:** `app/Console/Commands/PublicarArticulosProgramados.php`

**Lógica:**
```php
$articulos = Articulo::where('estado', 'programado')
    ->where('fecha_publicacion', '<=', now())
    ->get();

foreach ($articulos as $articulo) {
    $articulo->update(['estado' => 'publicado']);

    if ($articulo->enviar_newsletter) {
        dispatch(new EnviarNewsletterArticulo($articulo));
    }
}
```

**Registro en `Kernel.php`:** `->hourly()` — se ejecuta cada hora para respetar la hora de publicación con precisión razonable.

**Job `EnviarNewsletterArticulo`:**
- Obtiene todos los `Suscriptor` confirmados.
- Envía el nuevo Mailable `ArticuloPublicado` a cada uno (con throttling vía queue).
- `ArticuloPublicado` hereda la estructura del template de email existente, añadiendo título, extracto, imagen principal y enlace al artículo.

---

## 7. Integraciones en el blog público

### 7.1 Nueva ruta y vista de serie

```
GET /blog/serie/{slug}   BlogController@serie
```

**Vista `blog/serie.blade.php`:**
- Cabecera: nombre de la serie, descripción, categoría, total de artículos.
- Lista ordenada por `orden_en_serie`: título, extracto, fecha publicación, estado (publicado / próximamente).
- Solo muestra artículos con `estado = publicado` o `programado` (próximo).
- SEO: `meta_title` = "Serie: {nombre}", `meta_description` = descripción, schema `ItemList`.

### 7.2 Navegación anterior/siguiente en `blog/show.blade.php`

Si el artículo tiene `serie_id`:
- Bloque superior: "Parte {orden} de {total} de la serie: [{nombre serie}]({url serie})"
- Bloque inferior: `← Artículo anterior` y `Siguiente artículo →` (solo artículos publicados de la misma serie).

**Consultas en `BlogController@show`:**

```php
if ($articulo->serie_id) {
    $anterior = Articulo::where('serie_id', $articulo->serie_id)
        ->where('orden_en_serie', '<', $articulo->orden_en_serie)
        ->publicados()->orderByDesc('orden_en_serie')->first();

    $siguiente = Articulo::where('serie_id', $articulo->serie_id)
        ->where('orden_en_serie', '>', $articulo->orden_en_serie)
        ->publicados()->orderBy('orden_en_serie')->first();
}
```

### 7.3 Navegación del admin — nueva entrada

Añadir "📅 Calendario" en `layouts/admin.blade.php` entre "Artículos" y "Categorías".

---

## 8. IA — Enlazado automático en series (`AiInternalLinker`)

Al generar un artículo que pertenece a una serie, `AiArticleService` pasa al `AiInternalLinker` los IDs de los artículos anteriores de la misma serie (con `orden_en_serie < orden_actual` y `estado = publicado`). El linker los trata como enlaces prioritarios (forzados) antes de buscar en el resto del blog.

**Prerequisito:** el artículo debe existir ya en BD con `serie_id` y `orden_en_serie` asignados antes de lanzar la generación de contenido — esto se garantiza porque el flujo siempre crea primero el artículo (desde el tintero o desde "Programar") y solo luego permite generar contenido desde el editor.

Esto garantiza que el artículo 3 de una serie enlaza siempre a los artículos 1 y 2 si están publicados.

---

## 9. Fuera de scope (v1)

- Drag & drop real en el calendario (la reprogramación se hace desde el popover → "Cambiar fecha").
- Enlazado bidireccional (actualizar artículos anteriores cuando se publica uno nuevo).
- Generación masiva de contenido de una serie.
- Vista Semana interactiva.
- Schema markup `SeriesCollection` para SEO avanzado.

---

## 10. Resumen de archivos afectados / creados

### Nuevos
```
app/Models/Serie.php
app/Http/Controllers/Admin/CalendarioController.php
app/Http/Controllers/Admin/SerieController.php
app/Console/Commands/PublicarArticulosProgramados.php
app/Jobs/EnviarNewsletterArticulo.php
app/Mail/ArticuloPublicado.php
database/migrations/xxxx_add_serie_fields_to_articulos.php
database/migrations/xxxx_create_series_table.php
resources/views/admin/calendario/index.blade.php
resources/views/blog/serie.blade.php
resources/views/emails/articulo-publicado.blade.php
```

### Modificados
```
app/Models/Articulo.php              (serie_id, orden, enviar_newsletter, estado enum)
app/Http/Controllers/Admin/ArticuloController.php   (validar + nuevos campos)
app/Http/Controllers/BlogController.php             (método serie + anterior/siguiente)
app/Console/Kernel.php               (registro del comando horario)
resources/views/admin/articulos/_form.blade.php      (campos nuevos)
resources/views/admin/layouts/admin.blade.php        (nav Calendario)
resources/views/blog/show.blade.php  (bloque serie + anterior/siguiente)
routes/web.php                       (rutas nuevas)
```
