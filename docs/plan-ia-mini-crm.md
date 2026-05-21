# Plan de implementación: IA en mini-CRM (Fases 1 + 2)

> Estado: pendiente de implementación  
> Decisiones cerradas: Claude (texto) + OpenAI (SEO JSON, opcional) + Google Imagen / OpenAI Images (imagen)

---

## Objetivo

Añadir un asistente editorial IA dentro del CRUD de artículos que:

1. Genera el artículo completo + todos los campos SEO a partir de una idea corta.
2. Genera la imagen destacada y la descarga en `/storage/articulos/`.
3. Permite regenerar cada campo individualmente con botón ✨.
4. Consulta artículos anteriores para sugerir enlaces internos sutiles.
5. Es configurable desde el panel: proveedor, modelo y API key para texto e imagen.
6. Registra el coste estimado de cada generación.

Publicación siempre como **borrador**. El admin revisa y publica manualmente.

---

## Decisiones técnicas

| Aspecto | Decisión |
|---------|----------|
| Proveedor texto | Claude (Anthropic API, Http facade) |
| Proveedor SEO JSON | Mismo proveedor de texto (una sola llamada) |
| Proveedor imagen | Google Imagen 4 Fast / OpenAI Images (configurable) |
| API keys | Cifradas en BD con `encrypt()` / `decrypt()` |
| FAQ | Campo `faq_json` (TEXT JSON) en tabla `articulos` |
| Imagen | Descargada y guardada en `storage/app/public/articulos/` |
| `og_title` / `og_description` | Sin campos nuevos — `seo-head` ya usa `titulo`/`meta_description` como fallback |
| `secondary_keywords` | Fuera de scope — las etiquetas CSV son suficientes |
| Publicación automática | Nunca — siempre borrador |

---

## Arquitectura

```
Admin (browser)
  │
  ├── GET/PUT  /admin/ia/config            → IaConfigController
  ├── GET      /admin/ia/logs              → IaLogsController
  │
  ├── POST     /admin/ia/generate          → AiGenerateController@generate
  │             (sin article_id — artículo nuevo, datos en session/retorno JSON)
  ├── POST     /admin/articulos/{id}/ai/regenerate → AiGenerateController@regenerateField
  └── POST     /admin/articulos/{id}/ai/image      → AiGenerateController@generateImage

AiGenerateController
  └── AiArticleService                    (orquestador)
        ├── AiSettingsService             (lee config de BD)
        ├── AiInternalLinker              (busca artículos relevantes)
        ├── Providers/ClaudeProvider      (texto via Anthropic API)
        ├── Providers/OpenAiProvider      (alternativa texto/SEO)
        ├── ImageProviders/GoogleImageProvider
        ├── ImageProviders/OpenAiImageProvider
        └── AiCostLogger                  (guarda en ai_generations)
```

---

## 1. Base de datos

### 1a. Migración aditiva en `articulos`

```php
// database/migrations/2026_05_XX_add_ai_fields_to_articulos.php
$table->string('focus_keyword', 150)->nullable()->after('etiquetas');
$table->string('image_alt', 255)->nullable()->after('imagen_principal');
$table->text('faq_json')->nullable()->after('schema_type');   // JSON: [{question, answer}]
$table->text('ai_context_summary')->nullable();               // ~100 palabras para IA
$table->string('summary_short', 255)->nullable();             // ~25 palabras para admin
$table->boolean('ai_generated')->default(false);
$table->string('ai_last_provider', 50)->nullable();           // claude, openai, google
$table->string('ai_last_model', 100)->nullable();
$table->timestamp('ai_last_generated_at')->nullable();
```

### 1b. Tabla `ai_settings` (singleton — una sola fila)

```php
// database/migrations/2026_05_XX_create_ai_settings_table.php
Schema::create('ai_settings', function (Blueprint $table) {
    $table->id();
    // Proveedor de texto
    $table->string('text_provider', 30)->default('claude');   // claude, openai
    $table->string('text_model', 100)->default('claude-sonnet-4-6');
    $table->text('text_api_key')->nullable();                 // encrypt()
    // Proveedor de imagen
    $table->string('image_provider', 30)->default('google');  // google, openai
    $table->string('image_model', 100)->default('imagen-4.0-flash-exp');
    $table->text('image_api_key')->nullable();                // encrypt()
    $table->string('image_size', 20)->default('1024x1024');
    $table->string('image_style', 50)->nullable();
    // Prompts editables
    $table->longText('prompt_system')->nullable();            // system prompt base
    $table->longText('prompt_image')->nullable();
    $table->longText('prompt_interlinking')->nullable();
    // Parámetros globales
    $table->string('default_tone', 100)->default('profesional y cercano');
    $table->string('default_language', 10)->default('es');
    $table->string('default_length', 50)->default('1000-1500 palabras');
    $table->unsignedTinyInteger('max_articles_context')->default(5);
    $table->unsignedTinyInteger('max_internal_links')->default(2);
    $table->boolean('auto_generate_image')->default(false);
    $table->boolean('auto_generate_faq')->default(true);
    $table->boolean('always_draft')->default(true);
    $table->timestamps();
});
```

### 1c. Tabla `ai_generations` (log de costes)

```php
Schema::create('ai_generations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('article_id')->nullable()->constrained('articulos')->nullOnDelete();
    $table->string('provider', 30);                           // claude, openai, google
    $table->string('model', 100);
    $table->enum('type', ['full_article', 'image', 'field_regen']);
    $table->string('field_name', 50)->nullable();             // para field_regen
    $table->unsignedInteger('input_tokens')->nullable();
    $table->unsignedInteger('output_tokens')->nullable();
    $table->decimal('cost_usd', 8, 6)->nullable();
    $table->enum('status', ['ok', 'error'])->default('ok');
    $table->text('error_message')->nullable();
    $table->timestamp('created_at')->useCurrent();
});
```

---

## 2. Modelos

### `app/Models/AiSetting.php`

```php
class AiSetting extends Model
{
    protected $guarded = [];

    // Singleton: siempre devuelve la primera fila (o la crea con defaults)
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    // API keys cifradas
    public function getTextApiKeyAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }
    public function setTextApiKeyAttribute($value): void
    {
        $this->attributes['text_api_key'] = $value ? encrypt($value) : null;
    }
    // Idem para image_api_key
}
```

### `app/Models/AiGeneration.php`

```php
class AiGeneration extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    public function articulo(): BelongsTo { return $this->belongsTo(Articulo::class, 'article_id'); }
}
```

### Actualizar `app/Models/Articulo.php`

Añadir a `$fillable`: `focus_keyword`, `image_alt`, `faq_json`, `ai_context_summary`, `summary_short`, `ai_generated`, `ai_last_provider`, `ai_last_model`, `ai_last_generated_at`.

Añadir cast: `'faq_json' => 'array'`, `'ai_generated' => 'boolean'`.

---

## 3. Paquetes

```bash
composer require openai-php/laravel
```

Claude y Google Imagen usan `Http` facade de Laravel directamente (APIs REST sencillas, sin SDK).

Publicar config de OpenAI:
```bash
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

Añadir en `.env`:
```
OPENAI_API_KEY=   # si OpenAI es proveedor de texto o imagen
# Claude y Google keys se guardan en BD (encriptadas)
```

---

## 4. Servicios

### Estructura de archivos

```
app/Services/AI/
├── AiSettingsService.php
├── AiArticleService.php
├── AiInternalLinker.php
├── AiCostLogger.php
├── TextProviders/
│   ├── AiTextProviderInterface.php
│   ├── ClaudeTextProvider.php
│   └── OpenAiTextProvider.php
└── ImageProviders/
    ├── AiImageProviderInterface.php
    ├── GoogleImageProvider.php
    └── OpenAiImageProvider.php
```

### `AiTextProviderInterface`

```php
interface AiTextProviderInterface
{
    // Devuelve array con los campos generados
    public function generateArticle(array $input, string $systemPrompt): array;
    public function regenerateField(string $field, array $context, string $systemPrompt): string;
    // Para el log de coste
    public function lastUsage(): array; // ['input_tokens' => N, 'output_tokens' => N, 'cost_usd' => X]
}
```

### `ClaudeTextProvider` — llamada a Anthropic API

```php
// POST https://api.anthropic.com/v1/messages
// Headers: x-api-key, anthropic-version: 2023-06-01, content-type: application/json
// Body: { model, max_tokens, system, messages: [{role: user, content: prompt}] }
```

El response viene como `content[0].text` — JSON que parseamos.

Coste Claude claude-sonnet-4-6 (referencia agosto 2025): ~3$/1M input, ~15$/1M output.

### `GoogleImageProvider` — Google Imagen

```php
// POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateImages
// Query param: key={API_KEY}
// Body: { "instances": [{"prompt": "..."}], "parameters": {"sampleCount": 1} }
// Response: predictions[0].bytesBase64Encoded  (imagen en base64)
```

Guardado:
```php
$bytes = base64_decode($response['predictions'][0]['bytesBase64Encoded']);
$path  = 'articulos/' . Str::uuid() . '.jpg';
Storage::disk('public')->put($path, $bytes);
return Storage::disk('public')->url($path);
```

### `OpenAiImageProvider` — DALL-E 3

```php
// Usar el cliente openai-php/laravel:
$response = OpenAI::images()->create([
    'model'  => $model,
    'prompt' => $prompt,
    'size'   => $size,
    'n'      => 1,
]);
$url = $response->data[0]->url;
// Descargar URL temporal y guardar en storage
$bytes = Http::get($url)->body();
$path  = 'articulos/' . Str::uuid() . '.jpg';
Storage::disk('public')->put($path, $bytes);
```

### `AiArticleService` — orquestador del flujo completo

```php
public function generate(array $input): array
{
    $settings   = AiSettingsService::get();
    $provider   = $this->resolveTextProvider($settings);
    $linker     = app(AiInternalLinker::class);

    // 1. Contexto: artículos anteriores relevantes
    $articlesContext = $linker->findRelated(
        $input['focus_keyword'] ?? '',
        $input['categoria_id'] ?? null,
        $settings->max_articles_context
    );

    // 2. Construir prompt con contexto
    $systemPrompt = $settings->prompt_system ?: $this->defaultSystemPrompt($settings);
    $userPrompt   = $this->buildUserPrompt($input, $articlesContext, $settings);

    // 3. Llamar a la IA de texto
    $result = $provider->generateArticle($input, $systemPrompt . "\n\n" . $userPrompt);

    // 4. Log de coste
    AiCostLogger::log('full_article', $provider, $input['article_id'] ?? null);

    // 5. Imagen (si se pidió)
    if ($input['generate_image'] ?? false) {
        $imageProvider = $this->resolveImageProvider($settings);
        $imageUrl      = $imageProvider->generate($result['image_prompt'], $settings);
        $result['imagen_principal'] = $imageUrl;
        AiCostLogger::log('image', $imageProvider, $input['article_id'] ?? null);
    }

    // Asegurar siempre borrador
    $result['estado'] = $settings->always_draft ? 'borrador' : ($result['estado'] ?? 'borrador');

    return $result;
}
```

### `AiInternalLinker`

```php
public function findRelated(string $keyword, ?int $categoriaId, int $limit): array
{
    $articulos = Articulo::publicados()
        ->whereNotNull('ai_context_summary')
        ->select('titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
        ->orderByDesc('fecha_publicacion')
        ->get();

    // Scoring simple por overlap de palabras con la keyword buscada
    $keywords = array_filter(explode(' ', strtolower($keyword)));
    return $articulos
        ->map(function ($a) use ($keywords) {
            $text  = strtolower($a->titulo . ' ' . $a->focus_keyword . ' ' . $a->ai_context_summary);
            $score = array_sum(array_map(fn($k) => substr_count($text, $k), $keywords));
            return ['articulo' => $a, 'score' => $score];
        })
        ->sortByDesc('score')
        ->take($limit)
        ->pluck('articulo')
        ->map(fn($a) => [
            'titulo'            => $a->titulo,
            'slug'              => $a->slug,
            'focus_keyword'     => $a->focus_keyword,
            'ai_context_summary'=> $a->ai_context_summary,
        ])
        ->values()
        ->toArray();
}
```

### Prompt JSON que devuelve la IA

Claude devuelve **exclusivamente JSON válido** con esta estructura:

```json
{
  "titulo": "...",
  "slug": "...",
  "contenido": "...Markdown completo...",
  "extracto": "...",
  "focus_keyword": "...",
  "etiquetas": "tag1, tag2, tag3",
  "schema_type": "BlogPosting",
  "meta_title": "...",
  "meta_description": "...",
  "faq": [
    {"question": "...", "answer": "..."},
    {"question": "...", "answer": "..."}
  ],
  "image_prompt": "...en inglés...",
  "image_alt": "...",
  "ai_context_summary": "...~100 palabras para que otros artículos te mencionen...",
  "summary_short": "...~25 palabras para el admin...",
  "internal_links_suggested": [
    {"titulo": "...", "slug": "...", "anchor": "...", "razon": "..."}
  ]
}
```

---

## 5. Controladores

### `Admin/IaConfigController`

- `edit()` → vista `admin.ia.config` con `AiSetting::instance()`
- `update(Request $r)` → valida, cifra API keys si cambiaron, `AiSetting::instance()->update($data)`

### `Admin/IaLogsController`

- `index()` → `AiGeneration::with('articulo')->orderByDesc('created_at')->paginate(50)`
- Agrupados: coste total del mes, por artículo, por proveedor

### `Admin/AiGenerateController`

Todos los métodos devuelven JSON (son endpoints AJAX):

```php
// Para artículo nuevo (sin ID, datos en el request)
public function generate(Request $r): JsonResponse
{
    $result = $this->service->generate($r->validated());
    return response()->json(['ok' => true, 'data' => $result]);
}

// Regenerar un campo individual en artículo existente
public function regenerateField(Articulo $articulo, Request $r): JsonResponse
{
    $field  = $r->input('field');  // 'meta_title', 'extracto', etc.
    $result = $this->service->regenerateField($articulo, $field, $r->all());
    return response()->json(['ok' => true, 'field' => $field, 'value' => $result]);
}

// Generar solo imagen
public function generateImage(Articulo $articulo, Request $r): JsonResponse
{
    $url = $this->service->generateImage($articulo, $r->input('prompt'));
    $articulo->update(['imagen_principal' => $url]);
    return response()->json(['ok' => true, 'url' => $url]);
}
```

---

## 6. Rutas

```php
// En routes/web.php, dentro del grupo admin + middleware cms.auth:

Route::prefix('ia')->name('ia.')->group(function () {
    Route::get('config',  [IaConfigController::class, 'edit'])->name('config');
    Route::put('config',  [IaConfigController::class, 'update'])->name('config.update');
    Route::get('logs',    [IaLogsController::class, 'index'])->name('logs');
    // Generación sin article_id (artículo nuevo en memoria)
    Route::post('generate', [AiGenerateController::class, 'generate'])->name('generate');
});

Route::prefix('articulos/{articulo}/ai')->name('articulos.ai.')->group(function () {
    Route::post('regenerate', [AiGenerateController::class, 'regenerateField'])->name('regenerate');
    Route::post('image',      [AiGenerateController::class, 'generateImage'])->name('image');
});
```

---

## 7. Vistas

### Archivos nuevos/modificados

```
resources/views/admin/
├── ia/
│   ├── config.blade.php          ← nueva
│   └── logs.blade.php            ← nueva
└── articulos/
    ├── _ai_panel.blade.php       ← nueva (incluida en create + edit)
    └── _form.blade.php           ← modificada (campos nuevos + botones ✨)
```

### `_ai_panel.blade.php` — bloque superior del asistente

```html
<div class="ai-panel">
  <h3>✦ Asistente IA</h3>

  <!-- Formulario de idea -->
  <textarea id="ai-idea" placeholder="Escribe la idea del artículo..."></textarea>
  <div class="ai-row">
    <input id="ai-keyword" placeholder="Keyword principal">
    <input id="ai-localidad" placeholder="Localidad (opcional)">
    <select id="ai-tono">...</select>
    <input id="ai-instrucciones" placeholder="Instrucciones adicionales">
  </div>
  <div class="ai-checks">
    <label><input type="checkbox" id="ai-img" checked> Generar imagen</label>
    <label><input type="checkbox" id="ai-faq" checked> Generar FAQ</label>
    <label><input type="checkbox" id="ai-links" checked> Sugerir enlaces internos</label>
  </div>

  <button type="button" id="ai-generate-btn" class="btn-ai-primary">
    ✦ Generar con IA
  </button>
  <div id="ai-status" hidden>Generando... <span id="ai-spinner">⟳</span></div>
  <div id="ai-links-suggestions" hidden>
    <!-- Sugerencias de enlaces internos para aceptar/ignorar -->
  </div>
</div>
```

### `admin/ia/config.blade.php` — página de configuración

Cuatro secciones:

1. **Texto** — proveedor (select: claude/openai), modelo (text), API key (password), botón "Probar conexión"
2. **Imagen** — proveedor (select: google/openai), modelo, API key, tamaño, auto-generar (checkbox)
3. **Parámetros globales** — tono, idioma, longitud, max artículos contexto, max enlaces internos, siempre borrador, auto-FAQ
4. **Prompts editables** — tres `<textarea>` con los prompts internos (system, imagen, interlinking)

### `admin/ia/logs.blade.php`

Cabecera: coste total del mes | coste esta semana | generaciones totales

Tabla: fecha | artículo (enlace) | tipo | proveedor/modelo | tokens in/out | coste USD | estado

---

## 8. Cambios en `_form.blade.php`

### Campos nuevos a añadir

**Junto a Etiquetas:**
```html
<input type="text" name="focus_keyword" placeholder="fidelización comercio local">
<!-- Tooltip: "Keyword principal que Google debe asociar a este artículo. La IA la usa para optimizar el contenido." -->
```

**Junto a Imagen principal:**
```html
<input type="text" name="image_alt" placeholder="Descripción de la imagen para lectores de pantalla y Google">
```

**Sección FAQ (nueva, bajo Schema type):**
```html
<textarea id="faq_json" name="faq_json" rows="6" placeholder='[{"question":"...","answer":"..."}]'>
```
Nota: la vista pública `show.blade.php` debe renderizar este campo como HTML + schema `FAQPage`.

**Sección IA (nueva, colapsable, solo visible en edit):**
```html
<details class="ai-meta">
  <summary>Metadatos IA</summary>
  <textarea name="ai_context_summary" rows="4"><!-- resumen IA --></textarea>
  <input type="text" name="summary_short" placeholder="Resumen breve para el admin">
  <p>Último proveedor: {{ $a?->ai_last_provider }} / {{ $a?->ai_last_model }}</p>
  <p>Última generación: {{ $a?->ai_last_generated_at?->diffForHumans() }}</p>
</details>
```

### Botones ✨ en campos existentes

```html
<label class="form-label">
  Título *
  <span class="tip" ...>i</span>
  <button type="button" class="btn-regen" data-field="titulo" title="Regenerar con IA">✦</button>
</label>
```

Campos con botón ✨: `titulo`, `extracto`, `contenido`, `meta_title`, `meta_description`, `etiquetas`, `focus_keyword`, `faq_json`, `image_prompt` (visible solo en la sección IA), `image_alt`, `ai_context_summary`.

---

## 9. JavaScript (en `_ai_panel.blade.php`)

```javascript
// Generar artículo completo (nuevo)
document.getElementById('ai-generate-btn').addEventListener('click', async () => {
    const payload = {
        idea:           document.getElementById('ai-idea').value,
        focus_keyword:  document.getElementById('ai-keyword').value,
        localidad:      document.getElementById('ai-localidad').value,
        tono:           document.getElementById('ai-tono').value,
        instrucciones:  document.getElementById('ai-instrucciones').value,
        generate_image: document.getElementById('ai-img').checked,
        generate_faq:   document.getElementById('ai-faq').checked,
        suggest_links:  document.getElementById('ai-links').checked,
        categoria_id:   document.getElementById('categoria_blog_id').value,
    };

    showStatus('Generando artículo...');

    const res = await fetch('{{ route("admin.ia.generate") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload),
    });

    const json = await res.json();
    if (!json.ok) { showError(json.message); return; }

    // Poblar campos del formulario
    const d = json.data;
    setValue('titulo',           d.titulo);
    setValue('slug',             d.slug);
    setValue('extracto',         d.extracto);
    setValue('contenido',        d.contenido);
    setValue('focus_keyword',    d.focus_keyword);
    setValue('etiquetas',        d.etiquetas);
    setValue('meta_title',       d.meta_title);
    setValue('meta_description', d.meta_description);
    setValue('image_alt',        d.image_alt);
    setValue('faq_json',         JSON.stringify(d.faq, null, 2));
    setValue('ai_context_summary', d.ai_context_summary);
    setValue('summary_short',    d.summary_short);
    setValue('schema_type',      d.schema_type);

    if (d.imagen_principal) setValue('imagen_principal', d.imagen_principal);
    if (d.internal_links_suggested?.length) renderLinkSuggestions(d.internal_links_suggested);

    hideStatus();
});

// Regenerar campo individual
document.querySelectorAll('.btn-regen').forEach(btn => {
    btn.addEventListener('click', async () => {
        const field   = btn.dataset.field;
        const article = btn.dataset.articleId;  // undefined si artículo nuevo
        const url     = article
            ? `/admin/articulos/${article}/ai/regenerate`
            : '{{ route("admin.ia.generate") }}';  // fallback parcial

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ field, context: gatherContext() }),
        });
        const json = await res.json();
        if (json.ok) setValue(field, json.value);
    });
});
```

---

## 10. Vista pública — `show.blade.php`

Añadir renderizado de FAQ:

```php
// En el controlador BlogController::show()
$faq = $articulo->faq_json; // ya es array por el cast

// Si hay FAQ, añadir FAQPage al schema
if ($faq) {
    $faqSchema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => collect($faq)->map(fn($f) => [
            '@type'          => 'Question',
            'name'           => $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
        ])->all(),
    ];
    $schema = [$schema, $faqSchema]; // array de schemas
}
```

En la vista `show.blade.php`, sección FAQ:
```html
@if($articulo->faq_json)
<section class="art-faq">
    <h2>Preguntas frecuentes</h2>
    @foreach($articulo->faq_json as $faq)
    <details>
        <summary>{{ $faq['question'] }}</summary>
        <p>{{ $faq['answer'] }}</p>
    </details>
    @endforeach
</section>
@endif
```

---

## 11. Nav del admin

Añadir en `layouts/admin.blade.php`:

```html
<li><a href="{{ route('admin.ia.config') }}" class="{{ request()->is('admin/ia*') ? 'active' : '' }}">IA ✦</a></li>
```

---

## 12. Seeder de configuración por defecto

```php
// database/seeders/AiSettingSeeder.php
AiSetting::create([
    'text_provider' => 'claude',
    'text_model'    => 'claude-sonnet-4-6',
    'image_provider'=> 'google',
    'image_model'   => 'imagen-4.0-flash-exp',
    'image_size'    => '1024x1024',
    'auto_generate_image' => false,
    'auto_generate_faq'   => true,
    'always_draft'        => true,
    'max_articles_context'=> 5,
    'max_internal_links'  => 2,
    'default_tone'        => 'profesional y cercano',
    'default_language'    => 'es',
    'default_length'      => '1000-1500 palabras',
    'prompt_system'       => <<<'PROMPT'
Eres un redactor SEO experto en marketing local para comercios, bares, restaurantes y asociaciones de vecinos en España.
Escribes siempre en español de España.
Eventify es la plataforma para la que escribes: ayuda a comercios locales a captar clientes vía QR y fidelizarlos con campañas.
No inventes datos concretos, cifras legales ni estadísticas si no se proporcionan.
Devuelve EXCLUSIVAMENTE JSON válido con la estructura indicada. Sin texto adicional fuera del JSON.
PROMPT,
]);
```

---

## 13. Orden de implementación sugerido

1. **Migraciones** — `ai_settings`, `ai_generations`, campos aditivos en `articulos`
2. **Modelos** — `AiSetting`, `AiGeneration`, actualizar `Articulo`
3. **Seeder** — `AiSettingSeeder` con defaults y prompts
4. **Servicios** — `AiSettingsService` → `ClaudeTextProvider` → `AiInternalLinker` → `AiArticleService`
5. **Controlador** `IaConfigController` + vista `ia/config` → **probar que las API keys se guardan/recuperan**
6. **Controlador** `AiGenerateController` endpoint `generate` → **probar con Postman/artisan tinker**
7. **Panel `_ai_panel.blade.php`** + JS → integrar en `create.blade.php`
8. **Botones ✨** en `_form.blade.php` + endpoint `regenerateField`
9. **Proveedor de imagen** (`GoogleImageProvider` o `OpenAiImageProvider`) + endpoint `generateImage`
10. **Vista `ia/logs`** + `IaLogsController`
11. **Vista pública `show.blade.php`** — FAQ section + FAQPage schema

---

## 14. Estimación de coste por artículo

| Operación | Proveedor | Coste aprox. |
|-----------|-----------|-------------|
| Generar artículo completo (~1500 palabras, ~3000 tokens in + 2000 out) | Claude Sonnet | ~0,039 $ |
| Generar imagen | Google Imagen 4 Fast | ~0,02 $ |
| Regenerar un campo | Claude Sonnet | ~0,004 $ |
| **Total artículo con imagen** | | **~0,06 $** |

A 20 artículos/mes: ~1,20 $/mes. A 100 artículos/mes: ~6 $/mes.

---

## Fuera de scope (v2)

- Embeddings vectoriales para interlinking semántico real (ahora es keyword overlap)
- Soporte multi-usuario con logs por usuario
- Webhook para auto-actualizar `ai_context_summary` al editar un artículo
- Editor WYSIWYG para el contenido generado
- Plantillas editoriales predefinidas (bar, comercio, asociación...)
