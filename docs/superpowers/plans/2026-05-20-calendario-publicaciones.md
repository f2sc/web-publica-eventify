# Calendario de Publicaciones — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a publication calendar module with series management, AI planning panel, scheduled publishing (cron), newsletter dispatch, and public blog series pages.

**Architecture:** New `/admin/calendario` page with vanilla JS calendar grid and AJAX event loading. New `series` table with FK on `articulos`. `programado` added as a 4th estado. Hourly Laravel command auto-publishes scheduled articles. Newsletter dispatched via queued job. Public `/blog/serie/{slug}` page with prev/next article navigation. `AiInternalLinker` extended with forced IDs so series articles link back to previous installments.

**Tech Stack:** Laravel 9, PHP 8.1, MySQL, Blade, Vanilla JS (zero new frontend deps), PHPUnit

**Prerequisites:** Tests use the project's default `.env` MySQL DB (SQLite in-memory is commented out in `phpunit.xml`). Run `php artisan migrate` before the test suite if the DB is fresh. All admin routes require `session(['cms_token' => 'test'])` in tests because `CmsAuth` middleware checks `session()->has('cms_token')`.

---

## File map

| File | Action |
|------|--------|
| `database/migrations/2026_05_20_100000_create_series_table.php` | Create |
| `database/migrations/2026_05_20_100001_add_serie_fields_to_articulos.php` | Create |
| `app/Models/Serie.php` | Create |
| `app/Models/Articulo.php` | Modify |
| `routes/web.php` | Modify |
| `app/Http/Controllers/Admin/CalendarioController.php` | Create |
| `app/Http/Controllers/Admin/SerieController.php` | Create |
| `app/Services/AI/AiCalendarioService.php` | Create |
| `resources/views/admin/calendario/index.blade.php` | Create |
| `app/Http/Controllers/Admin/ArticuloController.php` | Modify |
| `resources/views/admin/articulos/_form.blade.php` | Modify |
| `resources/views/layouts/admin.blade.php` | Modify |
| `app/Jobs/EnviarNewsletterArticulo.php` | Create |
| `app/Mail/ArticuloPublicado.php` | Create |
| `resources/views/emails/articulo-publicado.blade.php` | Create |
| `app/Console/Commands/PublicarArticulosProgramados.php` | Create |
| `app/Console/Kernel.php` | Modify |
| `app/Http/Controllers/BlogController.php` | Modify |
| `resources/views/blog/serie.blade.php` | Create |
| `resources/views/blog/show.blade.php` | Modify |
| `app/Services/AI/AiInternalLinker.php` | Modify |
| `app/Services/AI/AiArticleService.php` | Modify |
| `resources/views/admin/articulos/_ai_panel.blade.php` | Modify |

---

### Task 1: Migration — Create `series` table

**Files:**
- Create: `database/migrations/2026_05_20_100000_create_series_table.php`
- Create: `tests/Unit/SeriesTableTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/SeriesTableTest.php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeriesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_series_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('series'));
        $this->assertTrue(Schema::hasColumns('series', [
            'id', 'nombre', 'slug', 'descripcion', 'categoria_blog_id', 'created_at', 'updated_at',
        ]));
    }

    public function test_series_slug_is_unique(): void
    {
        \DB::table('series')->insert(['nombre' => 'A', 'slug' => 'a', 'created_at' => now(), 'updated_at' => now()]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        \DB::table('series')->insert(['nombre' => 'B', 'slug' => 'a', 'created_at' => now(), 'updated_at' => now()]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter SeriesTableTest`
Expected: FAIL — "Table 'series' doesn't exist"

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_05_20_100000_create_series_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_blog_id')
                  ->nullable()
                  ->constrained('categorias_blog')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
```

- [ ] **Step 4: Migrate and run tests**

Run: `php artisan migrate && php artisan test --filter SeriesTableTest`
Expected: PASS (2 tests)

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_05_20_100000_create_series_table.php tests/Unit/SeriesTableTest.php
git commit -m "feat: add series table migration"
```

---

### Task 2: Migration — Add serie fields to `articulos` + `programado` estado

**Files:**
- Create: `database/migrations/2026_05_20_100001_add_serie_fields_to_articulos.php`
- Create: `tests/Unit/ArticulosSerieFieldsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/ArticulosSerieFieldsTest.php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArticulosSerieFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_articulos_has_serie_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('articulos', ['serie_id', 'orden_en_serie', 'enviar_newsletter']));
    }

    public function test_estado_accepts_programado(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Test', 'slug' => 'test-prog', 'estado' => 'programado',
            'schema_type' => 'BlogPosting', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertDatabaseHas('articulos', ['slug' => 'test-prog', 'estado' => 'programado']);
    }

    public function test_enviar_newsletter_defaults_to_true(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Test nl', 'slug' => 'test-nl', 'estado' => 'borrador',
            'schema_type' => 'BlogPosting', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertDatabaseHas('articulos', ['slug' => 'test-nl', 'enviar_newsletter' => true]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter ArticulosSerieFieldsTest`
Expected: FAIL — columns don't exist, enum rejects 'programado'

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_05_20_100001_add_serie_fields_to_articulos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->foreignId('serie_id')
                  ->nullable()->after('ai_last_generated_at')
                  ->constrained('series')->nullOnDelete();
            $table->unsignedSmallInteger('orden_en_serie')->nullable()->after('serie_id');
            $table->boolean('enviar_newsletter')->default(true)->after('orden_en_serie');
        });

        // MySQL ENUM modification — Blueprint cannot do this cleanly
        DB::statement("ALTER TABLE articulos MODIFY COLUMN estado ENUM('borrador','programado','publicado','archivado') DEFAULT 'borrador'");
    }

    public function down(): void
    {
        // Will fail if any row has estado='programado' — expected
        DB::statement("ALTER TABLE articulos MODIFY COLUMN estado ENUM('borrador','publicado','archivado') DEFAULT 'borrador'");

        Schema::table('articulos', function (Blueprint $table) {
            $table->dropForeign(['serie_id']);
            $table->dropColumn(['serie_id', 'orden_en_serie', 'enviar_newsletter']);
        });
    }
};
```

- [ ] **Step 4: Migrate and run tests**

Run: `php artisan migrate && php artisan test --filter ArticulosSerieFieldsTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_05_20_100001_add_serie_fields_to_articulos.php tests/Unit/ArticulosSerieFieldsTest.php
git commit -m "feat: add serie_id, orden_en_serie, enviar_newsletter, programado estado to articulos"
```

---

### Task 3: `Serie` model + `Articulo` model update

**Files:**
- Create: `app/Models/Serie.php`
- Modify: `app/Models/Articulo.php`
- Create: `tests/Unit/SerieModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/SerieModelTest.php
namespace Tests\Unit;

use App\Models\Articulo;
use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerieModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_serie(): void
    {
        $serie = Serie::create(['nombre' => 'IA para comercios', 'slug' => 'ia-para-comercios']);
        $this->assertDatabaseHas('series', ['slug' => 'ia-para-comercios']);
    }

    public function test_articulos_relation_ordered_by_orden(): void
    {
        $serie = Serie::create(['nombre' => 'Test', 'slug' => 'test-s']);
        \DB::table('articulos')->insert([
            ['titulo' => 'Art 2', 'slug' => 'art-2', 'estado' => 'borrador', 'schema_type' => 'BlogPosting',
             'serie_id' => $serie->id, 'orden_en_serie' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['titulo' => 'Art 1', 'slug' => 'art-1', 'estado' => 'borrador', 'schema_type' => 'BlogPosting',
             'serie_id' => $serie->id, 'orden_en_serie' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $this->assertEquals(1, $serie->fresh()->articulos->first()->orden_en_serie);
    }

    public function test_articulo_belongs_to_serie(): void
    {
        $serie = Serie::create(['nombre' => 'Test', 'slug' => 'test-b']);
        $id = \DB::table('articulos')->insertGetId([
            'titulo' => 'Art', 'slug' => 'art-b1', 'estado' => 'borrador',
            'schema_type' => 'BlogPosting', 'serie_id' => $serie->id,
            'enviar_newsletter' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $articulo = Articulo::find($id);
        $this->assertInstanceOf(Serie::class, $articulo->serie);
        $this->assertTrue($articulo->enviar_newsletter);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter SerieModelTest`
Expected: FAIL — "Class 'App\Models\Serie' not found"

- [ ] **Step 3: Create `Serie` model**

```php
<?php
// app/Models/Serie.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Serie extends Model
{
    protected $table = 'series';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'categoria_blog_id'];

    public function categoriaBlog(): BelongsTo
    {
        return $this->belongsTo(CategoriaBlog::class, 'categoria_blog_id');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'serie_id')->orderBy('orden_en_serie');
    }
}
```

- [ ] **Step 4: Update `Articulo` model**

In `app/Models/Articulo.php`, make three edits:

**4a — Add new fields to `$fillable`** (append after `'ai_last_generated_at'`):
```php
// Replace the existing $fillable array with:
protected $fillable = [
    'titulo', 'slug', 'extracto', 'contenido', 'imagen_principal', 'image_alt',
    'categoria_blog_id', 'categoria_blog', 'etiquetas', 'focus_keyword',
    'meta_title', 'meta_description', 'canonical', 'indexable', 'og_image',
    'schema_type', 'faq_json', 'autor', 'estado', 'fecha_publicacion',
    'ai_context_summary', 'summary_short', 'ai_generated',
    'ai_last_provider', 'ai_last_model', 'ai_last_generated_at',
    'serie_id', 'orden_en_serie', 'enviar_newsletter',
];
```

**4b — Add `enviar_newsletter` to `$casts`** (append after `'ai_last_generated_at' => 'datetime'`):
```php
'enviar_newsletter' => 'boolean',
```

**4c — Add `serie()` relation** (after the `categoriaBlog()` method):
```php
public function serie(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(Serie::class, 'serie_id');
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter SerieModelTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Models/Serie.php app/Models/Articulo.php tests/Unit/SerieModelTest.php
git commit -m "feat: Serie model + Articulo serie relation and new fillable fields"
```

---

### Task 4: Routes + stub controllers + stub views

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/Admin/CalendarioController.php` (stub)
- Create: `app/Http/Controllers/Admin/SerieController.php` (stub)
- Create: `resources/views/admin/calendario/index.blade.php` (stub)
- Create: `resources/views/blog/serie.blade.php` (stub)
- Create: `tests/Feature/RouteExistsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/RouteExistsTest.php
namespace Tests\Feature;

use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteExistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_calendario_returns_200(): void
    {
        $this->withSession(['cms_token' => 'test'])->get('/admin/calendario')
             ->assertStatus(200);
    }

    public function test_admin_calendario_events_returns_json(): void
    {
        $this->withSession(['cms_token' => 'test'])
             ->getJson('/admin/calendario/events?year=2026&month=5')
             ->assertStatus(200);
    }

    public function test_admin_series_store_returns_201(): void
    {
        $this->withSession(['cms_token' => 'test'])
             ->postJson('/admin/series', ['nombre' => 'Test Serie'])
             ->assertStatus(201);
    }

    public function test_blog_serie_404_for_unknown_slug(): void
    {
        $this->get('/blog/serie/no-existe')->assertStatus(404);
    }

    public function test_blog_serie_200_for_known_slug(): void
    {
        Serie::create(['nombre' => 'Mi serie', 'slug' => 'mi-serie']);
        $this->get('/blog/serie/mi-serie')->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter RouteExistsTest`
Expected: FAIL — 404 for all admin routes

- [ ] **Step 3: Add routes**

Edit `routes/web.php` — add imports at the top after existing `use` statements:
```php
use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\SerieController;
```

Inside the `middleware('cms.auth')->group(...)` block, after the `articulos/{articulo}/ai` block:
```php
// Calendario de publicaciones
Route::prefix('calendario')->name('calendario.')->group(function () {
    Route::get('/',                  [CalendarioController::class, 'index'])->name('index');
    Route::get('/events',            [CalendarioController::class, 'events'])->name('events');
    Route::post('/programar',        [CalendarioController::class, 'programarSerie'])->name('programar');
    Route::post('/ia/ideas',         [CalendarioController::class, 'iaIdeas'])->name('ia.ideas');
    Route::post('/ia/plan',          [CalendarioController::class, 'iaPlan'])->name('ia.plan');
    Route::post('/tintero/articulo', [CalendarioController::class, 'crearArticuloTintero'])->name('tintero.articulo');
    Route::post('/tintero/serie',    [CalendarioController::class, 'crearSerieTintero'])->name('tintero.serie');
});

// Series CRUD
Route::prefix('series')->name('series.')->group(function () {
    Route::get('/',           [SerieController::class, 'index'])->name('index');
    Route::post('/',          [SerieController::class, 'store'])->name('store');
    Route::put('/{serie}',    [SerieController::class, 'update'])->name('update');
    Route::delete('/{serie}', [SerieController::class, 'destroy'])->name('destroy');
});
```

In the public blog section, after `Route::get('/blog/categoria/{slug}', ...)`:
```php
Route::get('/blog/serie/{slug}', [BlogController::class, 'serie'])->name('blog.serie');
```

- [ ] **Step 4: Create stub controllers**

```php
<?php
// app/Http/Controllers/Admin/CalendarioController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('admin.calendario.index', [
            'series'    => collect(),
            'sueltos'   => collect(),
            'avisos'    => collect(),
            'categorias'=> collect(),
        ]);
    }
    public function events(Request $r): JsonResponse          { return response()->json([]); }
    public function programarSerie(Request $r): JsonResponse  { return response()->json(['ok' => true, 'fechas' => []]); }
    public function iaIdeas(Request $r): JsonResponse         { return response()->json(['ok' => true, 'ideas' => []]); }
    public function iaPlan(Request $r): JsonResponse          { return response()->json(['ok' => true, 'plan' => []]); }
    public function crearArticuloTintero(Request $r): JsonResponse { return response()->json(['ok' => true], 201); }
    public function crearSerieTintero(Request $r): JsonResponse    { return response()->json(['ok' => true], 201); }
}
```

```php
<?php
// app/Http/Controllers/Admin/SerieController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function index(): JsonResponse                      { return response()->json([]); }
    public function store(Request $r): JsonResponse           { return response()->json(['ok' => true], 201); }
    public function update(Request $r, $s): JsonResponse      { return response()->json(['ok' => true]); }
    public function destroy($s): JsonResponse                 { return response()->json(['ok' => true]); }
}
```

- [ ] **Step 5: Create stub views**

```blade
{{-- resources/views/admin/calendario/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Calendario')
@section('content')
<div>Calendario (en construcción)</div>
@endsection
```

In `resources/views/blog/serie.blade.php` (stub for now, full version in Task 13):
```blade
@extends('layouts.app')
@section('content')
<div class="container" style="padding:2rem">
    <h1>{{ $serie->nombre }}</h1>
</div>
@endsection
```

Add `serie()` stub to `app/Http/Controllers/BlogController.php` (after the `categoria()` method):
```php
public function serie(string $slug)
{
    $serie = \App\Models\Serie::where('slug', $slug)->firstOrFail();
    return view('blog.serie', ['serie' => $serie]);
}
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter RouteExistsTest`
Expected: PASS (5 tests)

- [ ] **Step 7: Commit**

```bash
git add routes/web.php app/Http/Controllers/Admin/CalendarioController.php app/Http/Controllers/Admin/SerieController.php resources/views/admin/calendario/index.blade.php resources/views/blog/serie.blade.php app/Http/Controllers/BlogController.php tests/Feature/RouteExistsTest.php
git commit -m "feat: add calendario, series and blog/serie routes with stubs"
```

---

### Task 5: `SerieController` — CRUD implementation

**Files:**
- Modify: `app/Http/Controllers/Admin/SerieController.php`
- Create: `tests/Feature/Admin/SerieControllerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/SerieControllerTest.php
namespace Tests\Feature\Admin;

use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerieControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['cms_token' => 'test'];

    public function test_store_creates_serie_with_auto_slug(): void
    {
        $this->withSession($this->auth)
             ->postJson('/admin/series', ['nombre' => 'IA para comercios'])
             ->assertStatus(201)
             ->assertJsonPath('ok', true)
             ->assertJsonPath('serie.slug', 'ia-para-comercios');
        $this->assertDatabaseHas('series', ['slug' => 'ia-para-comercios']);
    }

    public function test_store_validates_nombre_required(): void
    {
        $this->withSession($this->auth)
             ->postJson('/admin/series', ['nombre' => ''])
             ->assertStatus(422);
    }

    public function test_update_changes_nombre(): void
    {
        $s = Serie::create(['nombre' => 'Vieja', 'slug' => 'vieja']);
        $this->withSession($this->auth)
             ->putJson("/admin/series/{$s->id}", ['nombre' => 'Nueva'])
             ->assertStatus(200)->assertJsonPath('ok', true);
        $this->assertDatabaseHas('series', ['id' => $s->id, 'nombre' => 'Nueva']);
    }

    public function test_destroy_deletes_serie(): void
    {
        $s = Serie::create(['nombre' => 'Borrar', 'slug' => 'borrar']);
        $this->withSession($this->auth)
             ->deleteJson("/admin/series/{$s->id}")
             ->assertStatus(200)->assertJsonPath('ok', true);
        $this->assertDatabaseMissing('series', ['id' => $s->id]);
    }

    public function test_index_returns_list(): void
    {
        Serie::create(['nombre' => 'A', 'slug' => 'a']);
        Serie::create(['nombre' => 'B', 'slug' => 'b']);
        $this->withSession($this->auth)->getJson('/admin/series')
             ->assertStatus(200)->assertJsonCount(2);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter SerieControllerTest`
Expected: FAIL — stubs return empty/wrong responses

- [ ] **Step 3: Implement `SerieController`**

```php
<?php
// app/Http/Controllers/Admin/SerieController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SerieController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Serie::with('categoriaBlog')->orderBy('nombre')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['nombre']);
        $serie = Serie::create($data);
        return response()->json(['ok' => true, 'serie' => $serie], 201);
    }

    public function update(Request $request, Serie $serie): JsonResponse
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['nombre']);
        $serie->update($data);
        return response()->json(['ok' => true, 'serie' => $serie->fresh()]);
    }

    public function destroy(Serie $serie): JsonResponse
    {
        $serie->delete();
        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter SerieControllerTest`
Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/SerieController.php tests/Feature/Admin/SerieControllerTest.php
git commit -m "feat: implement SerieController CRUD"
```

---

### Task 6: `CalendarioController` — all endpoints + `AiCalendarioService`

**Files:**
- Create: `app/Services/AI/AiCalendarioService.php`
- Modify: `app/Http/Controllers/Admin/CalendarioController.php`
- Create: `tests/Feature/Admin/CalendarioControllerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/CalendarioControllerTest.php
namespace Tests\Feature\Admin;

use App\Models\Articulo;
use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['cms_token' => 'test'];

    public function test_events_returns_articles_for_month(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Art mayo', 'slug' => 'art-mayo', 'estado' => 'programado',
            'schema_type' => 'BlogPosting',
            'fecha_publicacion' => '2026-05-15 10:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession($this->auth)
             ->getJson('/admin/calendario/events?year=2026&month=5')
             ->assertStatus(200)
             ->assertJsonCount(1)
             ->assertJsonPath('0.titulo', 'Art mayo')
             ->assertJsonPath('0.estado', 'programado')
             ->assertJsonPath('0.contenido_vacio', true);
    }

    public function test_events_excludes_other_months(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Art junio', 'slug' => 'art-jun', 'estado' => 'publicado',
            'schema_type' => 'BlogPosting',
            'fecha_publicacion' => '2026-06-01 10:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession($this->auth)
             ->getJson('/admin/calendario/events?year=2026&month=5')
             ->assertStatus(200)->assertJsonCount(0);
    }

    public function test_programar_xdias_cadence(): void
    {
        $serie = Serie::create(['nombre' => 'T', 'slug' => 'tp']);
        foreach ([1, 2, 3] as $i) {
            \DB::table('articulos')->insert([
                'titulo' => "A{$i}", 'slug' => "tp-{$i}", 'estado' => 'borrador',
                'schema_type' => 'BlogPosting', 'serie_id' => $serie->id,
                'orden_en_serie' => $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->withSession($this->auth)->postJson('/admin/calendario/programar', [
            'serie_id'       => $serie->id,
            'start_datetime' => '2026-06-01 10:00',
            'cadencia'       => 'xdias',
            'cada_x_dias'    => 7,
        ])->assertStatus(200)->assertJsonPath('ok', true)->assertJsonCount(3, 'fechas');

        $this->assertDatabaseHas('articulos', ['slug' => 'tp-1', 'fecha_publicacion' => '2026-06-01 10:00:00']);
        $this->assertDatabaseHas('articulos', ['slug' => 'tp-2', 'fecha_publicacion' => '2026-06-08 10:00:00']);
        $this->assertDatabaseHas('articulos', ['slug' => 'tp-3', 'fecha_publicacion' => '2026-06-15 10:00:00']);
    }

    public function test_programar_semana_cadence(): void
    {
        $serie = Serie::create(['nombre' => 'S', 'slug' => 'ts']);
        foreach ([1, 2] as $i) {
            \DB::table('articulos')->insert([
                'titulo' => "S{$i}", 'slug' => "ts-{$i}", 'estado' => 'borrador',
                'schema_type' => 'BlogPosting', 'serie_id' => $serie->id,
                'orden_en_serie' => $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        // Start 2026-06-03 (Wednesday). Next Saturday (dow=6) is 2026-06-06
        $this->withSession($this->auth)->postJson('/admin/calendario/programar', [
            'serie_id'       => $serie->id,
            'start_datetime' => '2026-06-03 09:00',
            'cadencia'       => 'semana',
            'dia_semana'     => 6,
        ])->assertStatus(200)->assertJsonPath('ok', true);

        $this->assertDatabaseHas('articulos', ['slug' => 'ts-1', 'fecha_publicacion' => '2026-06-03 09:00:00']);
        $this->assertDatabaseHas('articulos', ['slug' => 'ts-2', 'fecha_publicacion' => '2026-06-06 09:00:00']);
    }

    public function test_crear_articulo_tintero(): void
    {
        $this->withSession($this->auth)
             ->postJson('/admin/calendario/tintero/articulo', ['titulo' => 'Nuevo borrador'])
             ->assertStatus(201)->assertJsonPath('ok', true);
        $this->assertDatabaseHas('articulos', ['titulo' => 'Nuevo borrador', 'estado' => 'borrador']);
    }

    public function test_crear_serie_tintero(): void
    {
        $this->withSession($this->auth)->postJson('/admin/calendario/tintero/serie', [
            'nombre'    => 'Mi serie',
            'articulos' => [['titulo' => 'Art 1'], ['titulo' => 'Art 2']],
        ])->assertStatus(201)->assertJsonPath('ok', true);

        $this->assertDatabaseHas('series', ['nombre' => 'Mi serie']);
        $this->assertDatabaseHas('articulos', ['titulo' => 'Art 1', 'orden_en_serie' => 1]);
        $this->assertDatabaseHas('articulos', ['titulo' => 'Art 2', 'orden_en_serie' => 2]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter CalendarioControllerTest`
Expected: FAIL — stubs return empty arrays

- [ ] **Step 3: Create `AiCalendarioService`**

```php
<?php
// app/Services/AI/AiCalendarioService.php
namespace App\Services\AI;

use Throwable;

class AiCalendarioService
{
    public function generateIdeas(string $descripcion, ?int $categoriaId = null): array
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        $prompt = "Dame exactamente 5 ideas de títulos SEO para artículos de blog sobre este tema:\n\n{$descripcion}\n\nDevuelve EXCLUSIVAMENTE este JSON (sin texto adicional):\n{\"items\":[\"Título 1\",\"Título 2\",\"Título 3\",\"Título 4\",\"Título 5\"]}";

        try {
            $result = $provider->generateArticle($prompt, $settings->prompt_system ?? '');
            $usage  = $provider->lastUsage();
            AiCostLogger::log('calendario_ideas', $provider->providerName(), $provider->modelName(), null,
                $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);
            return $result['items'] ?? [];
        } catch (Throwable $e) {
            AiCostLogger::log('calendario_ideas', $provider->providerName(), $provider->modelName(), null,
                status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }
    }

    public function generateSeriePlan(string $nombre, string $descripcion, int $n): array
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        $prompt = "Crea un plan de {$n} artículos de blog para la serie '{$nombre}'.\n\nDescripción / audiencia / objetivo: {$descripcion}\n\nDevuelve EXCLUSIVAMENTE este JSON:\n{\"plan\":[{\"orden\":1,\"titulo\":\"...\",\"descripcion\":\"...1-2 frases...\",\"enlaza_a\":[]}]}\n\nCada artículo indica en 'enlaza_a' los números de orden de artículos anteriores que debe enlazar.";

        try {
            $result = $provider->generateArticle($prompt, $settings->prompt_system ?? '');
            $usage  = $provider->lastUsage();
            AiCostLogger::log('calendario_plan', $provider->providerName(), $provider->modelName(), null,
                $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);
            return $result['plan'] ?? [];
        } catch (Throwable $e) {
            AiCostLogger::log('calendario_plan', $provider->providerName(), $provider->modelName(), null,
                status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }
    }
}
```

- [ ] **Step 4: Implement full `CalendarioController`**

```php
<?php
// app/Http/Controllers/Admin/CalendarioController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\CategoriaBlog;
use App\Models\Serie;
use App\Services\AI\AiCalendarioService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class CalendarioController extends Controller
{
    public function __construct(private readonly AiCalendarioService $iaService) {}

    public function index()
    {
        $series = Serie::with(['articulos' => function ($q) {
            $q->whereNull('fecha_publicacion')
              ->whereIn('estado', ['borrador', 'programado'])
              ->orderBy('orden_en_serie');
        }])->get()->filter(fn ($s) => $s->articulos->isNotEmpty())->values();

        $sueltos = Articulo::whereNull('fecha_publicacion')
            ->whereNull('serie_id')
            ->whereIn('estado', ['borrador', 'programado'])
            ->orderByDesc('created_at')
            ->get();

        $avisos = Articulo::whereIn('estado', ['programado', 'borrador'])
            ->whereNotNull('fecha_publicacion')
            ->whereBetween('fecha_publicacion', [now(), now()->addDays(7)])
            ->where(fn ($q) => $q->whereNull('contenido')->orWhere('contenido', ''))
            ->orderBy('fecha_publicacion')
            ->get();

        $categorias = CategoriaBlog::orderBy('nombre')->get();

        return view('admin.calendario.index', compact('series', 'sueltos', 'avisos', 'categorias'));
    }

    public function events(Request $request): JsonResponse
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        $articulos = Articulo::whereYear('fecha_publicacion', $year)
            ->whereMonth('fecha_publicacion', $month)
            ->whereNotNull('fecha_publicacion')
            ->whereIn('estado', ['borrador', 'programado', 'publicado'])
            ->select('id', 'titulo', 'slug', 'estado', 'fecha_publicacion', 'serie_id', 'contenido')
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'titulo'           => $a->titulo,
                'slug'             => $a->slug,
                'estado'           => $a->estado,
                'fecha_publicacion'=> $a->fecha_publicacion->format('Y-m-d H:i'),
                'serie_id'         => $a->serie_id,
                'contenido_vacio'  => empty($a->contenido),
            ]);

        return response()->json($articulos);
    }

    public function programarSerie(Request $request): JsonResponse
    {
        $request->validate([
            'serie_id'       => ['required', 'exists:series,id'],
            'start_datetime' => ['required', 'date_format:Y-m-d H:i'],
            'cadencia'       => ['required', 'in:xdias,semana,xsemanas'],
            'cada_x_dias'    => ['required_if:cadencia,xdias', 'integer', 'min:1'],
            'dia_semana'     => ['required_if:cadencia,semana', 'integer', 'between:0,6'],
            'cada_x_semanas' => ['required_if:cadencia,xsemanas', 'integer', 'min:1'],
        ]);

        $articulos  = Articulo::where('serie_id', $request->serie_id)->orderBy('orden_en_serie')->get();
        $startDate  = Carbon::createFromFormat('Y-m-d H:i', $request->start_datetime);
        $fechas     = [];
        $prev       = null;

        foreach ($articulos as $i => $articulo) {
            $fecha = match (true) {
                $i === 0                        => $startDate->copy(),
                $request->cadencia === 'xdias'  => $startDate->copy()->addDays($i * (int) $request->cada_x_dias),
                $request->cadencia === 'semana' => $this->nextWeekday($prev, (int) $request->dia_semana),
                default                         => $startDate->copy()->addWeeks($i * (int) $request->cada_x_semanas),
            };
            $prev = $fecha->copy();
            $articulo->update(['fecha_publicacion' => $fecha]);
            $fechas[] = ['id' => $articulo->id, 'titulo' => $articulo->titulo, 'fecha' => $fecha->format('Y-m-d H:i')];
        }

        return response()->json(['ok' => true, 'fechas' => $fechas]);
    }

    public function iaIdeas(Request $request): JsonResponse
    {
        $data = $request->validate([
            'descripcion'       => ['required', 'string', 'max:2000'],
            'categoria_blog_id' => ['nullable', 'integer'],
        ]);
        try {
            $ideas = $this->iaService->generateIdeas($data['descripcion'], $data['categoria_blog_id'] ?? null);
            return response()->json(['ok' => true, 'ideas' => $ideas]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function iaPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:255'],
            'descripcion'  => ['required', 'string', 'max:2000'],
            'n_articulos'  => ['required', 'integer', 'min:2', 'max:20'],
        ]);
        try {
            $plan = $this->iaService->generateSeriePlan($data['nombre'], $data['descripcion'], $data['n_articulos']);
            return response()->json(['ok' => true, 'plan' => $plan]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function crearArticuloTintero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);

        $articulo = Articulo::create([
            'titulo'            => $data['titulo'],
            'slug'              => Str::slug($data['titulo']) . '-' . substr(uniqid(), -4),
            'estado'            => 'borrador',
            'schema_type'       => 'BlogPosting',
            'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
            'enviar_newsletter' => true,
        ]);

        return response()->json(['ok' => true, 'articulo' => $articulo], 201);
    }

    public function crearSerieTintero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'             => ['required', 'string', 'max:255'],
            'categoria_blog_id'  => ['nullable', 'integer', 'exists:categorias_blog,id'],
            'articulos'          => ['required', 'array', 'min:2'],
            'articulos.*.titulo' => ['required', 'string', 'max:255'],
        ]);

        $serie  = Serie::create([
            'nombre'            => $data['nombre'],
            'slug'              => Str::slug($data['nombre']),
            'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
        ]);

        $creados = [];
        foreach ($data['articulos'] as $i => $item) {
            $creados[] = Articulo::create([
                'titulo'            => $item['titulo'],
                'slug'              => Str::slug($item['titulo']) . '-' . substr(uniqid(), -4),
                'estado'            => 'borrador',
                'schema_type'       => 'BlogPosting',
                'serie_id'          => $serie->id,
                'orden_en_serie'    => $i + 1,
                'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
                'enviar_newsletter' => true,
            ]);
        }

        return response()->json(['ok' => true, 'serie' => $serie, 'articulos' => $creados], 201);
    }

    private function nextWeekday(Carbon $after, int $dayOfWeek): Carbon
    {
        $date = $after->copy()->addDay();
        while ($date->dayOfWeek !== $dayOfWeek) {
            $date->addDay();
        }
        return $date;
    }
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter CalendarioControllerTest`
Expected: PASS (7 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Services/AI/AiCalendarioService.php app/Http/Controllers/Admin/CalendarioController.php tests/Feature/Admin/CalendarioControllerTest.php
git commit -m "feat: CalendarioController full implementation + AiCalendarioService"
```

---

### Task 7: Admin calendar view — full UI

**Files:**
- Modify: `resources/views/admin/calendario/index.blade.php`

No automated test for the full blade — manual verification checklist in spec section 11.2 and 11.3 covers this. The route test from Task 4 already confirms the page loads.

- [ ] **Step 1: Replace stub with full implementation**

```blade
{{-- resources/views/admin/calendario/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Calendario de publicaciones')

@push('head')
<style>
/* ─── Layout ─────────────────────────────────── */
.cal-wrap { display:grid; grid-template-columns:310px 1fr; gap:0; height:calc(100vh - 56px); overflow:hidden; }
.cal-sidebar { overflow-y:auto; border-right:1px solid #e5e7eb; background:#fafafa; padding:1rem; display:flex; flex-direction:column; gap:1rem; }
.cal-main { display:flex; flex-direction:column; overflow:hidden; }

/* ─── Avisos ──────────────────────────────────── */
.cal-avisos { background:#fff7ed; border-bottom:1px solid #fed7aa; padding:.5rem 1rem; font-size:.8rem; color:#c2410c; flex-shrink:0; }
.cal-avisos a { color:#c2410c; font-weight:600; text-decoration:none; margin-right:.75rem; }
.cal-avisos a:hover { text-decoration:underline; }

/* ─── Toolbar ─────────────────────────────────── */
.cal-toolbar { display:flex; align-items:center; gap:.5rem; padding:.6rem 1rem; border-bottom:1px solid #e5e7eb; flex-shrink:0; background:#fff; }
.cal-toolbar h2 { font-size:1rem; font-weight:700; margin:0 auto 0 .5rem; text-transform:capitalize; }
.cal-btn { border:1px solid #e5e7eb; background:#fff; border-radius:6px; padding:.3rem .65rem; font-size:.82rem; cursor:pointer; }
.cal-btn:hover { background:#f3f4f6; }
.cal-btn.active { background:#6366f1; color:#fff; border-color:#6366f1; }

/* ─── Grid ────────────────────────────────────── */
.cal-grid-wrap { flex:1; overflow-y:auto; padding:.5rem; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.cal-day-hdr { font-size:.72rem; font-weight:700; color:#6b7280; text-align:center; padding:.3rem 0; }
.cal-cell { min-height:90px; background:#fff; border:1px solid #f3f4f6; border-radius:6px; padding:.3rem .35rem; }
.cal-cell--today { border-color:#6366f1; background:#eef2ff; }
.cal-cell--empty { background:transparent; border-color:transparent; }
.cal-day-num { font-size:.75rem; font-weight:700; color:#374151; display:block; margin-bottom:.2rem; }
.cal-cell--today .cal-day-num { color:#6366f1; }
.cal-events { display:flex; flex-direction:column; gap:2px; }

/* ─── Event pills ─────────────────────────────── */
.cal-ev { border-radius:4px; padding:2px 5px; font-size:.7rem; cursor:pointer; display:flex; align-items:center; gap:3px; overflow:hidden; }
.cal-ev-title { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cal-ev-time { flex-shrink:0; font-size:.65rem; opacity:.8; }
.cal-ev--pub    { background:#d1fae5; color:#065f46; }
.cal-ev--prog   { background:#dbeafe; color:#1e40af; border:1px dashed #93c5fd; }
.cal-ev--bor    { background:#fef9c3; color:#854d0e; border:1px dashed #fde047; }
.cal-ev--warn   { background:#fee2e2; color:#991b1b; animation:cal-pulse 1.4s ease-in-out infinite; }
@keyframes cal-pulse { 0%,100%{opacity:1} 50%{opacity:.55} }

/* ─── IA Panel ────────────────────────────────── */
.ia-card { background:#f5f3ff; border:1.5px solid #c4b5fd; border-radius:10px; padding:1rem; }
.ia-title { font-size:.85rem; font-weight:700; color:#6d28d9; margin-bottom:.75rem; }
.ia-toggle { display:flex; border:1px solid #ddd6fe; border-radius:8px; overflow:hidden; margin-bottom:.85rem; }
.ia-toggle button { flex:1; padding:.35rem .4rem; background:#fff; border:none; font-size:.75rem; font-weight:600; color:#8b5cf6; cursor:pointer; }
.ia-toggle button.on { background:#7c3aed; color:#fff; }
.ia-lbl { display:block; font-size:.72rem; font-weight:600; color:#6d28d9; margin-bottom:.2rem; }
.ia-input, .ia-select, .ia-ta { width:100%; background:#fff; border:1px solid #ddd6fe; border-radius:6px; padding:.4rem .6rem; font-size:.8rem; color:#374151; box-sizing:border-box; }
.ia-ta { min-height:80px; resize:vertical; }
.ia-row { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-bottom:.5rem; }
.ia-field { margin-bottom:.5rem; }
.ia-btn { width:100%; background:#7c3aed; color:#fff; border:none; border-radius:8px; padding:.5rem; font-size:.82rem; font-weight:700; margin-top:.5rem; cursor:pointer; }
.ia-btn:disabled { opacity:.6; cursor:not-allowed; }
.ia-hint { font-size:.7rem; color:#8b5cf6; margin-top:.35rem; }
.ia-result { margin-top:.75rem; }
.ia-idea-item { display:flex; align-items:flex-start; gap:.4rem; font-size:.78rem; padding:.35rem 0; border-bottom:1px solid #ede9fe; }
.ia-idea-item:last-child { border-bottom:none; }
.ia-idea-add { flex-shrink:0; background:#ede9fe; color:#7c3aed; border:none; border-radius:4px; width:20px; height:20px; font-size:.85rem; cursor:pointer; line-height:1; }
.ia-plan-item { padding:.4rem 0; border-bottom:1px solid #ede9fe; }
.ia-plan-item:last-child { border-bottom:none; }
.ia-plan-orden { font-size:.68rem; font-weight:700; color:#6d28d9; }
.ia-plan-titulo { font-size:.78rem; font-weight:600; color:#374151; }
.ia-plan-desc { font-size:.72rem; color:#6b7280; margin-top:.1rem; }
.ia-plan-actions { display:flex; gap:.5rem; margin-top:.75rem; }
.ia-plan-btn { flex:1; padding:.4rem; font-size:.75rem; font-weight:600; border:none; border-radius:7px; cursor:pointer; }
.ia-plan-add { background:#d1fae5; color:#065f46; }
.ia-plan-prog { background:#6366f1; color:#fff; }

/* ─── Tintero ─────────────────────────────────── */
.tintero-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:.85rem; }
.tintero-title { font-size:.85rem; font-weight:700; color:#374151; margin-bottom:.5rem; display:flex; justify-content:space-between; align-items:center; }
.tintero-hint { font-size:.7rem; color:#9ca3af; margin-bottom:.6rem; }
.tintero-serie { margin-bottom:.5rem; }
.tintero-serie-hdr { display:flex; align-items:center; gap:.4rem; cursor:pointer; padding:.3rem .4rem; border-radius:6px; background:#f9fafb; }
.tintero-serie-hdr:hover { background:#f3f4f6; }
.tintero-serie-name { font-size:.78rem; font-weight:700; color:#374151; flex:1; }
.tintero-serie-count { font-size:.68rem; color:#9ca3af; }
.tintero-serie-body { padding:.3rem 0 0 .75rem; }
.tintero-art { display:flex; align-items:center; gap:.4rem; padding:.25rem 0; font-size:.75rem; color:#374151; }
.tintero-art-num { font-size:.65rem; color:#9ca3af; width:14px; flex-shrink:0; }
.tintero-art-title { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.tintero-badge { font-size:.6rem; padding:1px 5px; border-radius:99px; flex-shrink:0; }
.badge-bor { background:#fef9c3; color:#854d0e; }
.badge-prog { background:#dbeafe; color:#1e40af; }
.badge-prog-warn { background:#fee2e2; color:#991b1b; }
.tintero-art a { color:#6366f1; font-size:.68rem; margin-left:.3rem; text-decoration:none; flex-shrink:0; }
.tintero-sueltos-lbl { font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:.6rem 0 .3rem; }
.tintero-prog-btn { width:100%; background:#eef2ff; color:#4f46e5; border:1px dashed #a5b4fc; border-radius:7px; padding:.4rem; font-size:.75rem; font-weight:600; cursor:pointer; margin-top:.5rem; }
.tintero-prog-btn:hover { background:#e0e7ff; }

/* ─── Modal ───────────────────────────────────── */
.cal-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:flex; align-items:center; justify-content:center; }
.cal-modal-box { background:#fff; border-radius:12px; padding:1.5rem; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; }
.cal-modal-title { font-size:1rem; font-weight:800; margin-bottom:1rem; }
.cal-modal-lbl { display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
.cal-modal-input { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:.45rem .65rem; font-size:.85rem; box-sizing:border-box; margin-bottom:.75rem; }
.cal-cad-options { display:flex; gap:.4rem; margin-bottom:.75rem; }
.cal-cad-opt { padding:.35rem .65rem; border:1px solid #e5e7eb; border-radius:6px; font-size:.78rem; cursor:pointer; background:#fff; }
.cal-cad-opt.on { background:#6366f1; color:#fff; border-color:#6366f1; }
.cal-sub-input { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:.4rem .6rem; font-size:.82rem; box-sizing:border-box; margin-bottom:.5rem; }
.cal-preview-title { font-size:.78rem; font-weight:700; color:#374151; margin:.75rem 0 .4rem; }
.cal-preview-item { font-size:.75rem; color:#374151; padding:.2rem 0; border-bottom:1px solid #f3f4f6; display:flex; gap:.5rem; }
.cal-preview-item .warn { color:#ef4444; font-size:.65rem; }
.cal-modal-warn { font-size:.75rem; color:#92400e; background:#fef3c7; border-radius:6px; padding:.5rem .75rem; margin-top:.75rem; }
.cal-modal-actions { display:flex; gap:.75rem; margin-top:1rem; }
.cal-modal-ok { flex:1; background:#6366f1; color:#fff; border:none; border-radius:8px; padding:.55rem; font-size:.85rem; font-weight:700; cursor:pointer; }
.cal-modal-cancel { padding:.55rem 1rem; background:#fff; border:1px solid #e5e7eb; border-radius:8px; font-size:.85rem; cursor:pointer; }

/* ─── Popover ─────────────────────────────────── */
.cal-popover { position:fixed; z-index:999; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,.12); padding:1rem; width:260px; }
.cal-pop-title { font-weight:700; font-size:.88rem; margin-bottom:.5rem; }
.cal-pop-meta { font-size:.75rem; color:#6b7280; margin-bottom:.75rem; }
.cal-pop-actions { display:flex; flex-direction:column; gap:.35rem; }
.cal-pop-btn { display:block; text-align:left; width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:.4rem .65rem; font-size:.78rem; cursor:pointer; text-decoration:none; color:#374151; }
.cal-pop-btn:hover { background:#f3f4f6; }
.cal-pop-btn.danger { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
</style>
@endpush

@section('content')
<div class="cal-wrap">

  {{-- ═══ SIDEBAR ═══════════════════════════════════ --}}
  <aside class="cal-sidebar">

    {{-- IA PANEL --}}
    <div class="ia-card">
      <div class="ia-title">✦ IA — Planificar contenido</div>
      <div class="ia-toggle">
        <button id="ia-mode-single" class="on" onclick="setIaMode('single')">Artículo único</button>
        <button id="ia-mode-serie" onclick="setIaMode('serie')">Serie de artículos</button>
      </div>

      {{-- Modo: artículo único --}}
      <div id="ia-panel-single">
        <div class="ia-field">
          <label class="ia-lbl">Categoría</label>
          <select id="ia-cat-s" class="ia-select">
            <option value="">Sin categoría</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Describe el tema, audiencia y objetivo</label>
          <textarea id="ia-desc-s" class="ia-ta" placeholder="Quiero un artículo sobre cómo atraer clientes con IA en un comercio pequeño..."></textarea>
        </div>
        <button class="ia-btn" id="ia-btn-s" onclick="iaGenerarIdeas()">✦ Generar ideas de títulos</button>
        <div class="ia-hint">La IA sugerirá 5 títulos SEO que puedes añadir al tintero.</div>
        <div class="ia-result" id="ia-ideas-result" style="display:none"></div>
      </div>

      {{-- Modo: serie --}}
      <div id="ia-panel-serie" style="display:none">
        <div class="ia-row">
          <div>
            <label class="ia-lbl">Categoría</label>
            <select id="ia-cat-r" class="ia-select">
              <option value="">Sin categoría</option>
              @foreach($categorias as $cat)
              <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="ia-lbl">Nº artículos</label>
            <select id="ia-n" class="ia-select">
              @foreach([3,4,5,6,7,8,10] as $n)
              <option value="{{ $n }}" {{ $n===5?'selected':'' }}>{{ $n }} artículos</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Nombre de la serie</label>
          <input type="text" id="ia-nombre-r" class="ia-input" placeholder="IA para comercios locales">
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Describe el tema, audiencia y objetivo</label>
          <textarea id="ia-desc-r" class="ia-ta" placeholder="Quiero atraer más clientes a mi comercio físico usando IA. Audiencia: dueños sin conocimientos técnicos. Guía práctica."></textarea>
        </div>
        <button class="ia-btn" id="ia-btn-r" onclick="iaGenerarPlan()">✦ Generar plan de serie</button>
        <div class="ia-hint">La IA generará los títulos y cómo se enlazan entre sí.</div>
        <div class="ia-result" id="ia-plan-result" style="display:none"></div>
      </div>
    </div>

    {{-- TINTERO --}}
    <div class="tintero-card">
      <div class="tintero-title">
        <span>Tintero</span>
        <span style="font-size:.7rem;color:#9ca3af">{{ $sueltos->count() + $series->sum(fn($s)=>$s->articulos->count()) }} pendientes</span>
      </div>
      <div class="tintero-hint">↔ Artículos sin fecha asignada. Dales fecha desde el editor o con el modal de programación.</div>

      @foreach($series as $serie)
      <div class="tintero-serie">
        <div class="tintero-serie-hdr" onclick="toggleSerie({{ $serie->id }})">
          <span style="font-size:.8rem;color:#9ca3af" id="arrow-{{ $serie->id }}">▶</span>
          <span class="tintero-serie-name">{{ $serie->nombre }}</span>
          <span class="tintero-serie-count">{{ $serie->articulos->count() }} arts.</span>
          <button class="tintero-prog-btn" style="width:auto;margin:0;padding:.2rem .5rem;font-size:.68rem"
                  onclick="event.stopPropagation();abrirModalProgramar({{ $serie->id }}, '{{ addslashes($serie->nombre) }}')">📅</button>
        </div>
        <div class="tintero-serie-body" id="serie-body-{{ $serie->id }}" style="display:none">
          @foreach($serie->articulos as $art)
          @php
            $sinContenido = empty($art->contenido);
            $badgeClass = $sinContenido && $art->estado==='programado' ? 'badge-prog-warn' : ($art->estado==='programado' ? 'badge-prog' : 'badge-bor');
            $badgeLabel = $art->estado === 'programado' ? 'prog' : 'bor';
          @endphp
          <div class="tintero-art">
            <span class="tintero-art-num">{{ $art->orden_en_serie }}.</span>
            <span class="tintero-art-title" title="{{ $art->titulo }}">{{ $art->titulo }}</span>
            <span class="tintero-badge {{ $badgeClass }}">{{ $badgeLabel }}{{ $sinContenido ? ' ⚠' : '' }}</span>
            <a href="{{ route('admin.articulos.edit', $art) }}" title="Editar">✏</a>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach

      @if($sueltos->isNotEmpty())
      <div class="tintero-sueltos-lbl">Sueltos</div>
      @foreach($sueltos as $art)
      <div class="tintero-art">
        <span class="tintero-art-num">—</span>
        <span class="tintero-art-title" title="{{ $art->titulo }}">{{ $art->titulo }}</span>
        <span class="tintero-badge badge-bor">bor</span>
        <a href="{{ route('admin.articulos.edit', $art) }}" title="Editar">✏</a>
      </div>
      @endforeach
      @endif

      @if($series->isEmpty() && $sueltos->isEmpty())
      <p style="font-size:.78rem;color:#9ca3af;text-align:center;padding:.5rem 0">El tintero está vacío. ¡Usa el panel IA para generar ideas!</p>
      @endif
    </div>

  </aside>

  {{-- ═══ MAIN CALENDAR ══════════════════════════════ --}}
  <div class="cal-main">

    {{-- AVISO BAR --}}
    @if($avisos->isNotEmpty())
    <div class="cal-avisos">
      ⚠ Artículos sin contenido con fecha próxima (≤7 días):
      @foreach($avisos as $av)
      <a href="{{ route('admin.articulos.edit', $av) }}">{{ Str::limit($av->titulo, 40) }} ({{ $av->fecha_publicacion->format('d/m H:i') }})</a>
      @endforeach
    </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="cal-toolbar">
      <button class="cal-btn" onclick="navMes(-1)">‹</button>
      <button class="cal-btn" onclick="irHoy()">Hoy</button>
      <button class="cal-btn" onclick="navMes(1)">›</button>
      <h2 id="cal-mes-label"></h2>
    </div>

    {{-- GRID --}}
    <div class="cal-grid-wrap">
      <div class="cal-grid" id="cal-grid"></div>
    </div>
  </div>
</div>

{{-- MODAL PROGRAMAR SERIE --}}
<div class="cal-modal-bg" id="modal-bg" style="display:none" onclick="cerrarModal(event)">
  <div class="cal-modal-box" onclick="event.stopPropagation()">
    <div class="cal-modal-title" id="modal-title">📅 Programar en calendario</div>

    <label class="cal-modal-lbl">Serie</label>
    <input class="cal-modal-input" id="modal-serie-nombre" readonly style="background:#f9fafb">

    <label class="cal-modal-lbl">Fecha del primer artículo</label>
    <input type="date" class="cal-modal-input" id="modal-fecha" oninput="calcPreview()">

    <label class="cal-modal-lbl">Hora de publicación (todos)</label>
    <input type="time" class="cal-modal-input" id="modal-hora" value="09:00" oninput="calcPreview()">

    <label class="cal-modal-lbl">Cadencia</label>
    <div class="cal-cad-options">
      <button class="cal-cad-opt on" onclick="setCad('xdias',this)">Cada X días</button>
      <button class="cal-cad-opt" onclick="setCad('semana',this)">Día de la semana</button>
      <button class="cal-cad-opt" onclick="setCad('xsemanas',this)">Cada X semanas</button>
    </div>

    <div id="cad-xdias">
      <input type="number" class="cal-sub-input" id="cad-dias" value="7" min="1" oninput="calcPreview()" placeholder="Días entre artículos">
    </div>
    <div id="cad-semana" style="display:none">
      <select class="cal-sub-input" id="cad-dow" onchange="calcPreview()">
        <option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miércoles</option>
        <option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sábado</option>
        <option value="0">Domingo</option>
      </select>
    </div>
    <div id="cad-xsemanas" style="display:none">
      <input type="number" class="cal-sub-input" id="cad-semanas" value="2" min="1" oninput="calcPreview()" placeholder="Semanas entre artículos">
    </div>

    <div class="cal-preview-title">Vista previa de fechas</div>
    <div id="modal-preview"></div>

    <div class="cal-modal-warn">⚠ Los artículos permanecerán como borradores. Genera el contenido y cámbialo a «Programado» antes de la fecha de publicación.</div>

    <div class="cal-modal-actions">
      <button class="cal-modal-cancel" onclick="document.getElementById('modal-bg').style.display='none'">Cancelar</button>
      <button class="cal-modal-ok" id="modal-ok-btn" onclick="confirmarProgramar()">Confirmar</button>
    </div>
  </div>
</div>

{{-- POPOVER --}}
<div class="cal-popover" id="cal-popover" style="display:none">
  <div class="cal-pop-title" id="pop-titulo"></div>
  <div class="cal-pop-meta" id="pop-meta"></div>
  <div class="cal-pop-actions">
    <a class="cal-pop-btn" id="pop-editar" href="#">✏ Editar artículo</a>
    <a class="cal-pop-btn" id="pop-gen-ia" href="#" style="display:none">✦ Generar contenido con IA</a>
    <button class="cal-pop-btn" id="pop-change-fecha" onclick="popChangeFecha()">📅 Cambiar fecha</button>
    <a class="cal-pop-btn" id="pop-ver-blog" href="#" target="_blank" style="display:none">🔗 Ver en blog</a>
    <button class="cal-pop-btn" onclick="document.getElementById('cal-popover').style.display='none'">✕ Cerrar</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const BASE   = '{{ url("/admin/calendario") }}';
const ARTURL = '{{ url("/admin/articulos") }}';
const BLOGURL= '{{ url("/blog") }}';

let curYear  = new Date().getFullYear();
let curMonth = new Date().getMonth() + 1;
let evCache  = {};
let modalSerieId = null;
let modalArts    = [];
let cadencia     = 'xdias';
let popArtId     = null;

// ── Helpers ─────────────────────────────────────
function req(url, opts = {}) {
    return fetch(url, {
        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        ...opts,
    }).then(r => r.json());
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function pad(n) { return String(n).padStart(2,'0'); }

// ── Navigation ───────────────────────────────────
function navMes(d) { curMonth += d; if(curMonth>12){curMonth=1;curYear++;} if(curMonth<1){curMonth=12;curYear--;} loadMonth(); }
function irHoy()   { const t=new Date(); curYear=t.getFullYear(); curMonth=t.getMonth()+1; loadMonth(); }

async function loadMonth() {
    const key = `${curYear}-${curMonth}`;
    if (!evCache[key]) {
        const data = await req(`${BASE}/events?year=${curYear}&month=${curMonth}`);
        evCache[key] = Array.isArray(data) ? data : [];
    }
    render(curYear, curMonth, evCache[key]);
}

// ── Render calendar ──────────────────────────────
function render(y, m, evs) {
    const label = new Date(y, m-1, 1).toLocaleString('es-ES', {month:'long', year:'numeric'});
    document.getElementById('cal-mes-label').textContent = label.charAt(0).toUpperCase() + label.slice(1);

    const daysInMonth = new Date(y, m, 0).getDate();
    const firstDow    = new Date(y, m-1, 1).getDay();
    const todayStr    = new Date().toISOString().slice(0,10);

    const byDate = {};
    evs.forEach(e => { const d = e.fecha_publicacion.split(' ')[0]; (byDate[d] = byDate[d]||[]).push(e); });

    let html = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'].map(d => `<div class="cal-day-hdr">${d}</div>`).join('');
    for (let i = 0; i < firstDow; i++) html += `<div class="cal-cell cal-cell--empty"></div>`;

    for (let day = 1; day <= daysInMonth; day++) {
        const ds   = `${y}-${pad(m)}-${pad(day)}`;
        const arts = byDate[ds] || [];
        const today = ds === todayStr ? ' cal-cell--today' : '';
        html += `<div class="cal-cell${today}"><span class="cal-day-num">${day}</span><div class="cal-events">`;
        arts.forEach(e => {
            const cls = e.estado==='publicado' ? 'cal-ev--pub'
                      : (e.contenido_vacio && e.estado==='programado') ? 'cal-ev--warn'
                      : e.estado==='programado' ? 'cal-ev--prog' : 'cal-ev--bor';
            const hora = e.fecha_publicacion.split(' ')[1]?.slice(0,5) || '';
            const warn = e.contenido_vacio ? '⚠' : '';
            html += `<div class="cal-ev ${cls}" onclick="abrirPopover(${e.id}, event)">
                <span class="cal-ev-title">${esc(e.titulo)}</span>
                <span class="cal-ev-time">${hora}${warn}</span>
            </div>`;
        });
        html += `</div></div>`;
    }
    document.getElementById('cal-grid').innerHTML = html;
}

// ── Popover ──────────────────────────────────────
function abrirPopover(id, ev) {
    ev.stopPropagation();
    const key = `${curYear}-${curMonth}`;
    const art = evCache[key]?.find(a => a.id === id);
    if (!art) return;
    popArtId = id;
    const slug = art.slug;
    const hora = art.fecha_publicacion.split(' ')[1]?.slice(0,5) || '';
    const fecha= art.fecha_publicacion.split(' ')[0];
    document.getElementById('pop-titulo').textContent = art.titulo;
    document.getElementById('pop-meta').textContent   = `${fecha} ${hora} — ${art.estado}`;
    document.getElementById('pop-editar').href   = `${ARTURL}/${id}/edit`;
    const genIa = document.getElementById('pop-gen-ia');
    genIa.style.display = art.contenido_vacio ? 'block' : 'none';
    genIa.href = `${ARTURL}/${id}/edit`;
    const verBlog = document.getElementById('pop-ver-blog');
    verBlog.style.display = art.estado === 'publicado' ? 'block' : 'none';
    verBlog.href = `${BLOGURL}/${slug}`;
    const pop = document.getElementById('cal-popover');
    pop.style.display = 'block';
    const r = ev.target.getBoundingClientRect();
    pop.style.left = Math.min(r.left, window.innerWidth - 280) + 'px';
    pop.style.top  = (r.bottom + 4) + 'px';
}

document.addEventListener('click', e => {
    const pop = document.getElementById('cal-popover');
    if (pop.style.display !== 'none' && !pop.contains(e.target)) pop.style.display = 'none';
});

function popChangeFecha() {
    if (!popArtId) return;
    window.location.href = `${ARTURL}/${popArtId}/edit`;
}

// ── Tintero acordeón ─────────────────────────────
function toggleSerie(id) {
    const body  = document.getElementById(`serie-body-${id}`);
    const arrow = document.getElementById(`arrow-${id}`);
    const open  = body.style.display !== 'none';
    body.style.display  = open ? 'none' : 'block';
    arrow.textContent   = open ? '▶' : '▼';
}

// ── Modal programar ──────────────────────────────
async function abrirModalProgramar(serieId, nombre) {
    modalSerieId = serieId;
    document.getElementById('modal-serie-nombre').value = nombre;
    // Fetch articles in serie (those without fecha)
    const resp = await req(`${BASE}/events?year=2099&month=1`); // trick: use a far month to get empty
    // Actually fetch tintero articles for this serie via a workaround:
    // We just store series articles from the Blade-rendered tintero data
    modalArts = window.TINTERO_SERIES[serieId] || [];
    calcPreview();
    document.getElementById('modal-bg').style.display = 'flex';
}

function cerrarModal(e) {
    if (e.target === document.getElementById('modal-bg')) {
        document.getElementById('modal-bg').style.display = 'none';
    }
}

function setCad(c, btn) {
    cadencia = c;
    document.querySelectorAll('.cal-cad-opt').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    ['xdias','semana','xsemanas'].forEach(k => {
        document.getElementById(`cad-${k}`).style.display = k === c ? 'block' : 'none';
    });
    calcPreview();
}

function calcPreview() {
    const fechaStr = document.getElementById('modal-fecha').value;
    const hora     = document.getElementById('modal-hora').value || '09:00';
    if (!fechaStr) { document.getElementById('modal-preview').innerHTML = '<p style="font-size:.75rem;color:#9ca3af">Elige una fecha para ver la vista previa.</p>'; return; }

    let base = new Date(`${fechaStr}T${hora}`);
    const arts = modalArts;
    let html = '';

    arts.forEach((art, i) => {
        let d = new Date(base);
        if (i > 0) {
            if (cadencia === 'xdias') {
                const dias = parseInt(document.getElementById('cad-dias').value) || 7;
                d = new Date(base.getTime() + i * dias * 86400000);
            } else if (cadencia === 'semana') {
                const dow = parseInt(document.getElementById('cad-dow').value);
                let cur = new Date(base);
                let count = 0;
                while (count < i) { cur.setDate(cur.getDate()+1); if(cur.getDay()===dow) count++; }
                cur.setHours(base.getHours(), base.getMinutes());
                d = cur;
            } else {
                const sems = parseInt(document.getElementById('cad-semanas').value) || 2;
                d = new Date(base.getTime() + i * sems * 7 * 86400000);
            }
        }
        const dateLabel = d.toLocaleDateString('es-ES',{weekday:'short',day:'2-digit',month:'short'});
        const timeLabel = d.toTimeString().slice(0,5);
        const warn = art.sin_contenido ? '<span class="warn">⚠ sin contenido</span>' : '';
        html += `<div class="cal-preview-item"><span>${i+1}. ${esc(art.titulo)}</span><span>${dateLabel} ${timeLabel} ${warn}</span></div>`;
    });
    document.getElementById('modal-preview').innerHTML = html || '<p style="font-size:.75rem;color:#9ca3af">No hay artículos en el tintero para esta serie.</p>';
}

async function confirmarProgramar() {
    const fecha = document.getElementById('modal-fecha').value;
    const hora  = document.getElementById('modal-hora').value || '09:00';
    if (!fecha) { alert('Elige una fecha de inicio.'); return; }

    const btn = document.getElementById('modal-ok-btn');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const payload = {
        serie_id:       modalSerieId,
        start_datetime: `${fecha} ${hora}`,
        cadencia,
        cada_x_dias:    parseInt(document.getElementById('cad-dias').value) || 7,
        dia_semana:     parseInt(document.getElementById('cad-dow').value),
        cada_x_semanas: parseInt(document.getElementById('cad-semanas').value) || 2,
    };

    try {
        const res = await req(`${BASE}/programar`, { method:'POST', body: JSON.stringify(payload) });
        if (res.ok) {
            document.getElementById('modal-bg').style.display = 'none';
            evCache = {};
            loadMonth();
            location.reload(); // Reload to update tintero
        } else {
            alert('Error: ' + (res.message || 'Desconocido'));
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Confirmar';
    }
}

// ── IA Panel ─────────────────────────────────────
function setIaMode(mode) {
    document.getElementById('ia-panel-single').style.display = mode==='single' ? 'block' : 'none';
    document.getElementById('ia-panel-serie').style.display  = mode==='serie'  ? 'block' : 'none';
    document.getElementById('ia-mode-single').classList.toggle('on', mode==='single');
    document.getElementById('ia-mode-serie').classList.toggle('on', mode==='serie');
}

async function iaGenerarIdeas() {
    const desc = document.getElementById('ia-desc-s').value.trim();
    if (!desc) { alert('Escribe la descripción primero.'); return; }
    const btn = document.getElementById('ia-btn-s');
    btn.disabled = true; btn.textContent = '✦ Generando...';
    const catId = document.getElementById('ia-cat-s').value;
    try {
        const res = await req(`${BASE}/ia/ideas`, { method:'POST', body: JSON.stringify({ descripcion: desc, categoria_blog_id: catId||null }) });
        if (!res.ok) { alert('Error: ' + res.message); return; }
        const box = document.getElementById('ia-ideas-result');
        box.style.display = 'block';
        box.innerHTML = '<div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem">Ideas generadas:</div>' +
            (res.ideas||[]).map((t,i) => `<div class="ia-idea-item">
                <button class="ia-idea-add" onclick="addIdeaToTintero(${JSON.stringify(esc(t))}, ${catId||'null'})" title="Añadir al tintero">+</button>
                <span>${esc(t)}</span>
            </div>`).join('');
    } finally {
        btn.disabled = false; btn.textContent = '✦ Generar ideas de títulos';
    }
}

async function addIdeaToTintero(titulo, catId) {
    const res = await req(`${BASE}/tintero/articulo`, { method:'POST', body: JSON.stringify({ titulo, categoria_blog_id: catId }) });
    if (res.ok) { alert('Añadido al tintero. Recarga para verlo.'); }
}

async function iaGenerarPlan() {
    const nombre = document.getElementById('ia-nombre-r').value.trim();
    const desc   = document.getElementById('ia-desc-r').value.trim();
    const n      = parseInt(document.getElementById('ia-n').value);
    if (!nombre || !desc) { alert('Rellena el nombre y la descripción.'); return; }
    const btn = document.getElementById('ia-btn-r');
    btn.disabled = true; btn.textContent = '✦ Generando plan...';
    try {
        const res = await req(`${BASE}/ia/plan`, { method:'POST', body: JSON.stringify({ nombre, descripcion: desc, n_articulos: n }) });
        if (!res.ok) { alert('Error: ' + res.message); return; }
        const box = document.getElementById('ia-plan-result');
        box.style.display = 'block';
        const plan = res.plan || [];
        box.innerHTML = '<div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem">Plan generado:</div>' +
            plan.map(p => `<div class="ia-plan-item">
                <div class="ia-plan-orden">Parte ${p.orden}</div>
                <div class="ia-plan-titulo">${esc(p.titulo)}</div>
                <div class="ia-plan-desc">${esc(p.descripcion||'')}</div>
            </div>`).join('') +
            `<div class="ia-plan-actions">
                <button class="ia-plan-btn ia-plan-add" onclick="addPlanToTintero(${JSON.stringify(JSON.stringify(plan))}, '${esc(nombre)}')">+ Añadir al tintero</button>
                <button class="ia-plan-btn ia-plan-prog" onclick="addPlanAndProgram(${JSON.stringify(JSON.stringify(plan))}, '${esc(nombre)}')">📅 Programar en calendario</button>
            </div>`;
    } finally {
        btn.disabled = false; btn.textContent = '✦ Generar plan de serie';
    }
}

async function addPlanToTintero(planJson, nombre) {
    const plan   = JSON.parse(planJson);
    const catId  = document.getElementById('ia-cat-r').value || null;
    const res = await req(`${BASE}/tintero/serie`, { method:'POST', body: JSON.stringify({
        nombre, categoria_blog_id: catId,
        articulos: plan.map(p => ({ titulo: p.titulo })),
    })});
    if (res.ok) { alert('Serie y artículos añadidos al tintero. Recargando...'); location.reload(); }
    else alert('Error: ' + res.message);
}

async function addPlanAndProgram(planJson, nombre) {
    const plan   = JSON.parse(planJson);
    const catId  = document.getElementById('ia-cat-r').value || null;
    const res = await req(`${BASE}/tintero/serie`, { method:'POST', body: JSON.stringify({
        nombre, categoria_blog_id: catId,
        articulos: plan.map(p => ({ titulo: p.titulo })),
    })});
    if (res.ok) {
        modalArts    = res.articulos.map(a => ({ titulo: a.titulo, sin_contenido: true }));
        modalSerieId = res.serie.id;
        document.getElementById('modal-serie-nombre').value = nombre;
        calcPreview();
        document.getElementById('modal-bg').style.display = 'flex';
    } else { alert('Error: ' + res.message); }
}

// ── Tintero series data for modal ────────────────
window.TINTERO_SERIES = {
    @foreach($series as $serie)
    {{ $serie->id }}: {!! json_encode($serie->articulos->map(fn($a) => ['id' => $a->id, 'titulo' => $a->titulo, 'sin_contenido' => empty($a->contenido)])->values()) !!},
    @endforeach
};

// ── Init ─────────────────────────────────────────
loadMonth();
</script>
@endpush
```

- [ ] **Step 2: Test manually**

Run: `php artisan serve`
Visit: `http://localhost:8000/admin/calendario` (with active session)
Expected:
- Two-column layout renders without JS errors (check browser console)
- Calendar grid shows current month with day numbers
- Month navigation (‹ ›) changes the month and reloads events
- Tintero shows articles (check with some borrador articles without `fecha_publicacion` in the DB)
- IA panel toggles between single/serie modes
- "Hoy" button returns to current month

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/calendario/index.blade.php
git commit -m "feat: admin calendar view — full UI with tintero, IA panel, calendar grid and scheduling modal"
```

---

### Task 8: `ArticuloController` + `_form.blade.php` — new fields

**Files:**
- Modify: `app/Http/Controllers/Admin/ArticuloController.php`
- Modify: `resources/views/admin/articulos/_form.blade.php`
- Create: `tests/Feature/Admin/ArticuloSerieFieldsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/ArticuloSerieFieldsTest.php
namespace Tests\Feature\Admin;

use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticuloSerieFieldsTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['cms_token' => 'test'];

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'titulo'       => 'Test artículo',
            'slug'         => '',
            'estado'       => 'borrador',
            'schema_type'  => 'BlogPosting',
            'indexable'    => '1',
        ], $overrides);
    }

    public function test_store_accepts_programado_estado(): void
    {
        $this->withSession($this->auth)
             ->post('/admin/articulos', $this->basePayload(['estado' => 'programado']))
             ->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'Test artículo', 'estado' => 'programado']);
    }

    public function test_store_persists_serie_id_and_orden(): void
    {
        $serie = Serie::create(['nombre' => 'S', 'slug' => 's']);
        $this->withSession($this->auth)->post('/admin/articulos', $this->basePayload([
            'titulo'         => 'Art serie',
            'serie_id'       => $serie->id,
            'orden_en_serie' => 2,
        ]))->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'Art serie', 'serie_id' => $serie->id, 'orden_en_serie' => 2]);
    }

    public function test_store_persists_enviar_newsletter_false(): void
    {
        $this->withSession($this->auth)->post('/admin/articulos', $this->basePayload([
            'titulo'           => 'No newsletter',
            'enviar_newsletter'=> '0',
        ]))->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'No newsletter', 'enviar_newsletter' => false]);
    }

    public function test_datetime_local_fecha_validacion(): void
    {
        $this->withSession($this->auth)->post('/admin/articulos', $this->basePayload([
            'titulo'           => 'Con hora',
            'fecha_publicacion'=> '2026-06-15T10:30',
        ]))->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'Con hora']);
        $art = \App\Models\Articulo::where('titulo', 'Con hora')->first();
        $this->assertEquals('10:30', $art->fecha_publicacion->format('H:i'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter ArticuloSerieFieldsTest`
Expected: FAIL — `programado` rejected by validation, unknown fields ignored

- [ ] **Step 3: Update `ArticuloController::validar()`**

Edit `app/Http/Controllers/Admin/ArticuloController.php` — replace the `validar()` method:

```php
private function validar(Request $request): array
{
    return $request->validate([
        'titulo'              => ['required', 'string', 'max:255'],
        'slug'                => ['nullable', 'string', 'max:255'],
        'extracto'            => ['nullable', 'string'],
        'contenido'           => ['nullable', 'string'],
        'imagen_principal'    => ['nullable', 'string', 'max:500'],
        'image_alt'           => ['nullable', 'string', 'max:255'],
        'focus_keyword'       => ['nullable', 'string', 'max:150'],
        'etiquetas'           => ['nullable', 'string', 'max:255'],
        'meta_title'          => ['nullable', 'string', 'max:255'],
        'meta_description'    => ['nullable', 'string', 'max:320'],
        'canonical'           => ['nullable', 'url', 'max:255'],
        'indexable'           => ['boolean'],
        'og_image'            => ['nullable', 'string', 'max:500'],
        'schema_type'         => ['required', 'string', 'in:BlogPosting,Article,HowTo'],
        'faq_json'            => ['nullable', 'string'],
        'autor'               => ['nullable', 'string', 'max:100'],
        'estado'              => ['required', 'in:borrador,programado,publicado,archivado'],
        'fecha_publicacion'   => ['nullable', 'date_format:Y-m-d\TH:i'],
        'ai_context_summary'  => ['nullable', 'string'],
        'summary_short'       => ['nullable', 'string', 'max:255'],
        // Serie fields
        'serie_id'            => ['nullable', 'integer', 'exists:series,id'],
        'orden_en_serie'      => ['nullable', 'integer', 'min:1'],
        'enviar_newsletter'   => ['boolean'],
    ]);
}
```

- [ ] **Step 4: Update `_form.blade.php` — add serie + newsletter fields**

In `resources/views/admin/articulos/_form.blade.php`, locate the `{{-- Estado y fecha --}}` block (around line 202) and insert a new block just before it:

```blade
{{-- Serie y orden --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="serie_id">
            Serie
            <span class="tip" tabindex="0" data-tip="Si este artículo pertenece a una serie, selecciónala. Aparecerá la navegación anterior/siguiente en el blog y la IA enlazará con los artículos anteriores de la misma serie.">i</span>
        </label>
        <select id="serie_id" name="serie_id" class="form-control" onchange="toggleOrden(this.value)">
            <option value="">Sin serie (artículo suelto)</option>
            @foreach($series ?? [] as $serie)
            <option value="{{ $serie->id }}" {{ old('serie_id', $a?->serie_id) == $serie->id ? 'selected' : '' }}>
                {{ $serie->nombre }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="form-group" id="orden-group" style="{{ old('serie_id', $a?->serie_id) ? '' : 'display:none' }}">
        <label class="form-label" for="orden_en_serie">
            Orden en la serie
            <span class="tip" tabindex="0" data-tip="Posición de este artículo dentro de la serie (1 = primero). Determina la navegación anterior/siguiente en el blog.">i</span>
        </label>
        <input type="number" id="orden_en_serie" name="orden_en_serie" class="form-control" min="1"
               value="{{ old('orden_en_serie', $a?->orden_en_serie) }}" placeholder="1">
    </div>
</div>
```

In the `{{-- Estado y fecha --}}` block, replace the estado `<select>` options line:
```blade
{{-- OLD --}}
@foreach(['borrador' => 'Borrador', 'publicado' => 'Publicado', 'archivado' => 'Archivado'] as $val => $label)

{{-- NEW --}}
@foreach(['borrador' => 'Borrador', 'programado' => 'Programado', 'publicado' => 'Publicado', 'archivado' => 'Archivado'] as $val => $label)
```

After the fecha_publicacion field (after line `</div>` closing the form-row), add the newsletter checkbox:
```blade
{{-- Enviar newsletter --}}
<div class="form-group" style="margin-top:.5rem">
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer">
        <input type="hidden" name="enviar_newsletter" value="0">
        <input type="checkbox" name="enviar_newsletter" value="1"
               {{ old('enviar_newsletter', $a?->enviar_newsletter ?? true) ? 'checked' : '' }}>
        <span>Enviar newsletter al publicar</span>
        <span class="tip" tabindex="0" data-tip="Si está marcado, se enviará un email a todos los suscriptores confirmados cuando el artículo se publique (ya sea manualmente o mediante el cron de autopublicación).">i</span>
    </label>
</div>
```

Add JS for toggle at the bottom of `@push('scripts')` in `_form.blade.php` (or in an inline script tag at the end of the file):
```blade
@push('scripts')
<script>
function toggleOrden(serieId) {
    document.getElementById('orden-group').style.display = serieId ? 'block' : 'none';
}
</script>
@endpush
```

- [ ] **Step 5: Update `ArticuloController::edit()` and `create()` to pass `$series`**

Edit `app/Http/Controllers/Admin/ArticuloController.php`:

```php
// Replace create() method
public function create()
{
    $categorias = CategoriaBlog::orderBy('nombre')->get();
    $series     = \App\Models\Serie::orderBy('nombre')->get();
    return view('admin.articulos.create', compact('categorias', 'series'));
}

// Replace edit() method
public function edit(Articulo $articulo)
{
    $categorias = CategoriaBlog::orderBy('nombre')->get();
    $series     = \App\Models\Serie::orderBy('nombre')->get();
    return view('admin.articulos.edit', compact('articulo', 'categorias', 'series'));
}
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter ArticuloSerieFieldsTest`
Expected: PASS (4 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/ArticuloController.php resources/views/admin/articulos/_form.blade.php tests/Feature/Admin/ArticuloSerieFieldsTest.php
git commit -m "feat: add serie, orden, enviar_newsletter fields to article form + programado estado validation"
```

---

### Task 9: Admin nav — add Calendario link

**Files:**
- Modify: `resources/views/layouts/admin.blade.php`
- Create: `tests/Feature/Admin/AdminNavTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/AdminNavTest.php
namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_nav_includes_calendario_link(): void
    {
        $this->withSession(['cms_token' => 'test'])
             ->get('/admin/articulos')
             ->assertStatus(200)
             ->assertSee('Calendario');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter AdminNavTest`
Expected: FAIL — "Calendario" not in nav

- [ ] **Step 3: Edit nav**

In `resources/views/layouts/admin.blade.php`, find the nav `<ul>` block and add the Calendario link after the "Artículos" item:

```blade
{{-- Find this line: --}}
<li><a href="{{ route('admin.articulos.index') }}" class="{{ request()->is('admin/articulos*') && !request()->is('admin/articulos/*/ai*') ? 'active' : '' }}">Artículos</a></li>

{{-- Insert immediately after: --}}
<li><a href="{{ route('admin.calendario.index') }}" class="{{ request()->is('admin/calendario*') ? 'active' : '' }}">📅 Calendario</a></li>
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter AdminNavTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/admin.blade.php tests/Feature/Admin/AdminNavTest.php
git commit -m "feat: add Calendario link to admin nav"
```

---

### Task 10: Newsletter — `EnviarNewsletterArticulo` job + `ArticuloPublicado` mailable + email template

**Files:**
- Create: `app/Jobs/EnviarNewsletterArticulo.php`
- Create: `app/Mail/ArticuloPublicado.php`
- Create: `resources/views/emails/articulo-publicado.blade.php`
- Create: `tests/Feature/NewsletterTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/NewsletterTest.php
namespace Tests\Feature;

use App\Jobs\EnviarNewsletterArticulo;
use App\Mail\ArticuloPublicado;
use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticulo(): Articulo
    {
        $id = \DB::table('articulos')->insertGetId([
            'titulo' => 'Nuevo artículo', 'slug' => 'nuevo-art', 'estado' => 'publicado',
            'schema_type' => 'BlogPosting', 'extracto' => 'Un extracto breve.',
            'fecha_publicacion' => now(), 'enviar_newsletter' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return Articulo::find($id);
    }

    private function makeSuscriptor(bool $confirmado = true): Suscriptor
    {
        return Suscriptor::create([
            'nombre' => 'Test', 'email' => 'test@example.com',
            'token_confirmacion' => 'tok123', 'confirmado' => $confirmado,
        ]);
    }

    public function test_job_sends_mail_to_confirmed_suscriptores(): void
    {
        Mail::fake();
        $art = $this->makeArticulo();
        $this->makeSuscriptor(true);
        $this->makeSuscriptor(false); // unconfirmed — should NOT receive

        dispatch(new EnviarNewsletterArticulo($art));

        Mail::assertSent(ArticuloPublicado::class, 1);
        Mail::assertSent(ArticuloPublicado::class, fn ($m) => $m->hasTo('test@example.com'));
    }

    public function test_job_does_not_send_to_unsubscribed(): void
    {
        Mail::fake();
        $art = $this->makeArticulo();
        Suscriptor::create([
            'nombre' => 'Dado de baja', 'email' => 'baja@example.com',
            'token_confirmacion' => 'tok456', 'confirmado' => true,
            'unsubscribed_at' => now(),
        ]);
        dispatch(new EnviarNewsletterArticulo($art));
        Mail::assertNothingSent();
    }

    public function test_mailable_renders_article_data(): void
    {
        $art = $this->makeArticulo();
        $sus = $this->makeSuscriptor();
        $mail = new ArticuloPublicado($art, $sus);
        $rendered = $mail->build();
        $this->assertNotNull($rendered);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter NewsletterTest`
Expected: FAIL — classes don't exist

- [ ] **Step 3: Create the Job**

```php
<?php
// app/Jobs/EnviarNewsletterArticulo.php
namespace App\Jobs;

use App\Mail\ArticuloPublicado;
use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarNewsletterArticulo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Articulo $articulo) {}

    public function handle(): void
    {
        $suscriptores = Suscriptor::where('confirmado', true)
            ->whereNull('unsubscribed_at')
            ->get();

        foreach ($suscriptores as $suscriptor) {
            Mail::to($suscriptor->email)
                ->send(new ArticuloPublicado($this->articulo, $suscriptor));
        }
    }
}
```

- [ ] **Step 4: Create the Mailable**

```php
<?php
// app/Mail/ArticuloPublicado.php
namespace App\Mail;

use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArticuloPublicado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Articulo   $articulo,
        public Suscriptor $suscriptor
    ) {}

    public function build(): static
    {
        return $this->subject('Nuevo artículo en el blog de Eventify: ' . $this->articulo->titulo)
                    ->view('emails.articulo-publicado');
    }
}
```

- [ ] **Step 5: Create the email template**

```blade
{{-- resources/views/emails/articulo-publicado.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $articulo->titulo }}</title>
</head>
<body style="margin:0;padding:0;background:#f9f5ff;font-family:'Helvetica Neue',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f5ff;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(109,0,126,.08);">

        <tr>
          <td style="background:linear-gradient(135deg,#6d007e,#b12140);padding:32px 40px;text-align:center;">
            <p style="margin:0;font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">Eventify</p>
            <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,.75);">Blog de comercio local</p>
          </td>
        </tr>

        @if($articulo->imagen_principal)
        <tr>
          <td style="padding:0;">
            <img src="{{ $articulo->imagen_principal }}" alt="{{ $articulo->image_alt ?? $articulo->titulo }}"
                 style="width:100%;max-height:240px;object-fit:cover;display:block;">
          </td>
        </tr>
        @endif

        <tr>
          <td style="padding:40px 40px 32px;">
            @if($articulo->categoria_blog)
            <p style="margin:0 0 8px;font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.08em;">
              {{ $articulo->categoria_blog }}
            </p>
            @endif

            <h1 style="margin:0 0 16px;font-size:22px;font-weight:900;color:#1f2937;line-height:1.3;">
              {{ $articulo->titulo }}
            </h1>

            @if($articulo->extracto)
            <p style="margin:0 0 28px;font-size:15px;color:#4b5563;line-height:1.7;">
              {{ $articulo->extracto }}
            </p>
            @endif

            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td align="center" style="padding-bottom:32px;">
                  <a href="{{ url('/blog/' . $articulo->slug) }}"
                     style="display:inline-block;background:linear-gradient(135deg,#6d007e,#b12140);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:10px;">
                    Leer el artículo →
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
              Recibes este email porque te suscribiste al blog de Eventify.
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#f9f5ff;padding:20px 40px;border-top:1px solid #ede9fe;">
            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
              © {{ date('Y') }} Eventify &mdash;
              <a href="{{ url('/privacidad') }}" style="color:#6d007e;">Privacidad</a>
              &mdash;
              <a href="{{ url('/newsletter/cancelar/' . $suscriptor->token_confirmacion) }}" style="color:#6d007e;">Darme de baja</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter NewsletterTest`
Expected: PASS (3 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Jobs/EnviarNewsletterArticulo.php app/Mail/ArticuloPublicado.php resources/views/emails/articulo-publicado.blade.php tests/Feature/NewsletterTest.php
git commit -m "feat: newsletter job, mailable and email template for published articles"
```

---

### Task 11: `PublicarArticulosProgramados` command + Kernel

**Files:**
- Create: `app/Console/Commands/PublicarArticulosProgramados.php`
- Modify: `app/Console/Kernel.php`
- Create: `tests/Feature/PublicarArticulosProgramadosTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/PublicarArticulosProgramadosTest.php
namespace Tests\Feature;

use App\Jobs\EnviarNewsletterArticulo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PublicarArticulosProgramadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_programado_article_with_past_fecha(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Pasado', 'slug' => 'pasado', 'estado' => 'programado',
            'schema_type' => 'BlogPosting', 'enviar_newsletter' => false,
            'fecha_publicacion' => now()->subHour(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('app:publicar-articulos-programados')->assertSuccessful();
        $this->assertDatabaseHas('articulos', ['slug' => 'pasado', 'estado' => 'publicado']);
    }

    public function test_does_not_publish_borrador(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Bor', 'slug' => 'bor', 'estado' => 'borrador',
            'schema_type' => 'BlogPosting',
            'fecha_publicacion' => now()->subHour(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('app:publicar-articulos-programados')->assertSuccessful();
        $this->assertDatabaseHas('articulos', ['slug' => 'bor', 'estado' => 'borrador']);
    }

    public function test_does_not_publish_future_programado(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Futuro', 'slug' => 'futuro', 'estado' => 'programado',
            'schema_type' => 'BlogPosting',
            'fecha_publicacion' => now()->addDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('app:publicar-articulos-programados')->assertSuccessful();
        $this->assertDatabaseHas('articulos', ['slug' => 'futuro', 'estado' => 'programado']);
    }

    public function test_dispatches_newsletter_job_when_enabled(): void
    {
        Queue::fake();
        \DB::table('articulos')->insert([
            'titulo' => 'NL', 'slug' => 'nl', 'estado' => 'programado',
            'schema_type' => 'BlogPosting', 'enviar_newsletter' => true,
            'fecha_publicacion' => now()->subMinute(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('app:publicar-articulos-programados')->assertSuccessful();
        Queue::assertPushed(EnviarNewsletterArticulo::class);
    }

    public function test_does_not_dispatch_newsletter_when_disabled(): void
    {
        Queue::fake();
        \DB::table('articulos')->insert([
            'titulo' => 'NoNL', 'slug' => 'nonl', 'estado' => 'programado',
            'schema_type' => 'BlogPosting', 'enviar_newsletter' => false,
            'fecha_publicacion' => now()->subMinute(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('app:publicar-articulos-programados')->assertSuccessful();
        Queue::assertNotPushed(EnviarNewsletterArticulo::class);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter PublicarArticulosProgramadosTest`
Expected: FAIL — command not found

- [ ] **Step 3: Create the command**

```php
<?php
// app/Console/Commands/PublicarArticulosProgramados.php
namespace App\Console\Commands;

use App\Jobs\EnviarNewsletterArticulo;
use App\Models\Articulo;
use Illuminate\Console\Command;

class PublicarArticulosProgramados extends Command
{
    protected $signature   = 'app:publicar-articulos-programados';
    protected $description = 'Publica artículos con estado=programado cuya fecha_publicacion ya ha pasado';

    public function handle(): int
    {
        $articulos = Articulo::where('estado', 'programado')
            ->where('fecha_publicacion', '<=', now())
            ->get();

        foreach ($articulos as $articulo) {
            $articulo->update(['estado' => 'publicado']);
            $this->line("Publicado: [{$articulo->id}] {$articulo->titulo}");

            if ($articulo->enviar_newsletter) {
                dispatch(new EnviarNewsletterArticulo($articulo));
            }
        }

        $this->info("Procesados: {$articulos->count()} artículos.");
        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Register command in `Kernel.php`**

Edit `app/Console/Kernel.php` — replace the `schedule()` method:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:publicar-articulos-programados')->hourly();
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter PublicarArticulosProgramadosTest`
Expected: PASS (5 tests)

- [ ] **Step 6: Verify cron registration**

Run: `php artisan schedule:list`
Expected: `app:publicar-articulos-programados` appears with "Every hour" frequency.

- [ ] **Step 7: Commit**

```bash
git add app/Console/Commands/PublicarArticulosProgramados.php app/Console/Kernel.php tests/Feature/PublicarArticulosProgramadosTest.php
git commit -m "feat: hourly cron command to auto-publish programado articles + newsletter dispatch"
```

---

### Task 12: `BlogController` — `serie()` method + `anterior/siguiente` in `show()`

**Files:**
- Modify: `app/Http/Controllers/BlogController.php`
- Create: `tests/Feature/BlogSerieTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/BlogSerieTest.php
namespace Tests\Feature;

use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogSerieTest extends TestCase
{
    use RefreshDatabase;

    private function makeSerie(): Serie
    {
        return Serie::create(['nombre' => 'IA Comercios', 'slug' => 'ia-comercios',
            'descripcion' => 'Serie sobre IA para comercios.']);
    }

    private function makeArt(Serie $serie, int $orden, string $estado = 'publicado'): int
    {
        return \DB::table('articulos')->insertGetId([
            'titulo' => "Art {$orden}", 'slug' => "art-{$orden}", 'estado' => $estado,
            'schema_type' => 'BlogPosting', 'serie_id' => $serie->id, 'orden_en_serie' => $orden,
            'fecha_publicacion' => now()->subDays(10 - $orden),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_serie_page_returns_200(): void
    {
        $serie = $this->makeSerie();
        $this->get('/blog/serie/ia-comercios')->assertStatus(200)->assertSee('IA Comercios');
    }

    public function test_serie_page_404_for_unknown_slug(): void
    {
        $this->get('/blog/serie/no-existe')->assertStatus(404);
    }

    public function test_show_includes_anterior_siguiente_for_serie_article(): void
    {
        $serie = $this->makeSerie();
        $this->makeArt($serie, 1);
        $this->makeArt($serie, 2);
        $this->makeArt($serie, 3);

        $response = $this->get('/blog/art-2');
        $response->assertStatus(200);
        $response->assertViewHas('anterior');
        $response->assertViewHas('siguiente');
        $this->assertEquals('Art 1', $response->viewData('anterior')->titulo);
        $this->assertEquals('Art 3', $response->viewData('siguiente')->titulo);
    }

    public function test_show_first_article_has_no_anterior(): void
    {
        $serie = $this->makeSerie();
        $this->makeArt($serie, 1);
        $this->makeArt($serie, 2);
        $response = $this->get('/blog/art-1');
        $response->assertStatus(200);
        $this->assertNull($response->viewData('anterior'));
        $this->assertNotNull($response->viewData('siguiente'));
    }

    public function test_show_non_serie_article_has_no_nav(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Suelto', 'slug' => 'suelto', 'estado' => 'publicado',
            'schema_type' => 'BlogPosting', 'fecha_publicacion' => now()->subDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $response = $this->get('/blog/suelto');
        $response->assertStatus(200);
        $this->assertNull($response->viewData('anterior'));
        $this->assertNull($response->viewData('siguiente'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter BlogSerieTest`
Expected: FAIL — `serie()` is a stub, `show()` doesn't pass anterior/siguiente

- [ ] **Step 3: Update `BlogController`**

Replace the stub `serie()` method and update `show()`:

```php
public function serie(string $slug)
{
    $serie = \App\Models\Serie::with(['articulos' => function ($q) {
        $q->whereIn('estado', ['publicado', 'programado'])->orderBy('orden_en_serie');
    }, 'categoriaBlog'])->where('slug', $slug)->firstOrFail();

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Serie: ' . $serie->nombre,
        'description'     => $serie->descripcion,
        'numberOfItems'   => $serie->articulos->where('estado', 'publicado')->count(),
        'itemListElement' => $serie->articulos->where('estado', 'publicado')->values()
            ->map(fn ($a, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => url("/blog/{$a->slug}"), 'name' => $a->titulo])
            ->all(),
    ];

    return view('blog.serie', [
        'title'       => 'Serie: ' . $serie->nombre . ' — Blog Eventify',
        'description' => $serie->descripcion ?? "Serie de artículos: {$serie->nombre}",
        'canonical'   => url("/blog/serie/{$slug}"),
        'schema'      => $schema,
        'serie'       => $serie,
    ]);
}
```

In `show()`, add anterior/siguiente logic just before the `return view(...)` call. Find the line `return view('blog.show', [` and insert before it:

```php
// Navegación anterior/siguiente (solo para artículos de una serie)
$anterior  = null;
$siguiente = null;
if ($articulo->serie_id) {
    $anterior = Articulo::where('serie_id', $articulo->serie_id)
        ->where('orden_en_serie', '<', $articulo->orden_en_serie)
        ->publicados()
        ->orderByDesc('orden_en_serie')
        ->first();

    $siguiente = Articulo::where('serie_id', $articulo->serie_id)
        ->where('orden_en_serie', '>', $articulo->orden_en_serie)
        ->publicados()
        ->orderBy('orden_en_serie')
        ->first();
}
```

Add `$anterior`, `$siguiente` and `$serie` to the view data array:
```php
return view('blog.show', [
    // ... existing keys ...
    'articulo'  => $articulo,
    'anterior'  => $anterior,
    'siguiente' => $siguiente,
]);
```

Also add `use App\Models\Serie;` to `BlogController.php` imports (or use `\App\Models\Serie::` inline as done in the `serie()` method above).

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter BlogSerieTest`
Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/BlogController.php tests/Feature/BlogSerieTest.php
git commit -m "feat: BlogController serie page + anterior/siguiente nav in show()"
```

---

### Task 13: `blog/serie.blade.php` — full series page

**Files:**
- Modify: `resources/views/blog/serie.blade.php`

No automated test — `BlogSerieTest` from Task 12 confirms the page loads and data is correct.

- [ ] **Step 1: Replace stub with full implementation**

```blade
{{-- resources/views/blog/serie.blade.php --}}
@extends('layouts.app')

@section('content')

{{-- Hero --}}
<div style="background:linear-gradient(135deg,#6d007e,#b12140);padding:3rem 0 2rem">
  <div class="container">
    @if($serie->categoriaBlog)
    <a href="/blog/categoria/{{ $serie->categoriaBlog->slug }}"
       style="display:inline-block;background:rgba(255,255,255,.18);color:#fff;font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:99px;text-decoration:none;margin-bottom:1rem;text-transform:uppercase;letter-spacing:.05em">
      {{ $serie->categoriaBlog->nombre }}
    </a>
    @endif
    <p style="color:rgba(255,255,255,.7);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.5rem">Serie de artículos</p>
    <h1 style="color:#fff;font-size:2rem;font-weight:900;margin:0 0 1rem;line-height:1.2">{{ $serie->nombre }}</h1>
    @if($serie->descripcion)
    <p style="color:rgba(255,255,255,.85);font-size:1.05rem;max-width:600px;line-height:1.6;margin:0">{{ $serie->descripcion }}</p>
    @endif
    <p style="color:rgba(255,255,255,.6);font-size:.85rem;margin-top:1rem">
      {{ $serie->articulos->where('estado','publicado')->count() }} artículos publicados
      @if($serie->articulos->where('estado','programado')->count() > 0)
       · {{ $serie->articulos->where('estado','programado')->count() }} próximamente
      @endif
    </p>
  </div>
</div>

{{-- Article list --}}
<div class="container" style="padding:2.5rem 1rem;max-width:760px">
  <ol style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:1.25rem">
    @foreach($serie->articulos as $art)
    @php $publicado = $art->estado === 'publicado'; @endphp
    <li style="display:flex;gap:1.25rem;align-items:flex-start;background:#fff;border:1px solid #f3f4f6;border-radius:12px;padding:1.25rem;{{ !$publicado ? 'opacity:.65' : '' }}">
      <div style="width:36px;height:36px;background:{{ $publicado ? 'linear-gradient(135deg,#6d007e,#b12140)' : '#e5e7eb' }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem;font-weight:800;color:{{ $publicado ? '#fff' : '#9ca3af' }}">
        {{ $art->orden_en_serie }}
      </div>
      <div style="flex:1;min-width:0">
        @if($publicado)
        <a href="/blog/{{ $art->slug }}" style="font-size:1rem;font-weight:700;color:#1f2937;text-decoration:none;line-height:1.3;display:block;margin-bottom:.35rem">
          {{ $art->titulo }}
        </a>
        @else
        <p style="font-size:1rem;font-weight:700;color:#6b7280;margin:0 0 .35rem;line-height:1.3">
          {{ $art->titulo }}
        </p>
        @endif
        @if($art->extracto)
        <p style="font-size:.875rem;color:#6b7280;margin:0 0 .5rem;line-height:1.5">{{ Str::limit($art->extracto, 120) }}</p>
        @endif
        <div style="display:flex;align-items:center;gap:.75rem;font-size:.78rem">
          @if($publicado && $art->fecha_publicacion)
          <span style="color:#9ca3af">{{ $art->fecha_publicacion->locale('es')->isoFormat('D MMM YYYY') }}</span>
          @endif
          @if(!$publicado)
          <span style="background:#dbeafe;color:#1e40af;padding:.15rem .6rem;border-radius:99px;font-weight:600">Próximamente</span>
          @endif
          @if($publicado)
          <a href="/blog/{{ $art->slug }}" style="color:#7c3aed;font-weight:600;text-decoration:none">Leer →</a>
          @endif
        </div>
      </div>
    </li>
    @endforeach
  </ol>

  <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #f3f4f6;text-align:center">
    <a href="/blog" style="color:#7c3aed;font-size:.875rem;text-decoration:none;font-weight:600">← Volver al blog</a>
  </div>
</div>

@endsection
```

- [ ] **Step 2: Test manually**

Run: `php artisan serve`
- Create a serie with articles and visit `/blog/serie/{slug}`.
- Articles show numbered, with "Leer →" for published and "Próximamente" pill for programado.
- Hero shows correct category, name, description and count.

- [ ] **Step 3: Commit**

```bash
git add resources/views/blog/serie.blade.php
git commit -m "feat: blog serie page — full article list with prev/next progress display"
```

---

### Task 14: `blog/show.blade.php` — series banner + anterior/siguiente navigation

**Files:**
- Modify: `resources/views/blog/show.blade.php`

- [ ] **Step 1: Add series banner just after the hero block ends**

In `resources/views/blog/show.blade.php`, find the `{{-- ═══ CUERPO DEL ARTÍCULO ═══ --}}` comment and insert this block before the `<div class="art-layout container">` line:

```blade
{{-- Serie banner --}}
@if($articulo->serie_id && $articulo->serie)
@php $totalEnSerie = $articulo->serie->articulos()->publicados()->count(); @endphp
<div style="background:#f5f3ff;border-bottom:1px solid #ede9fe;padding:.65rem 0">
  <div class="container" style="font-size:.82rem;color:#7c3aed;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
    <span style="font-weight:700">Serie:</span>
    <a href="{{ route('blog.serie', $articulo->serie->slug) }}" style="color:#7c3aed;font-weight:700;text-decoration:none">
      {{ $articulo->serie->nombre }}
    </a>
    <span style="color:#a78bfa">— Parte {{ $articulo->orden_en_serie }} de {{ $totalEnSerie }}</span>
  </div>
</div>
@endif
```

- [ ] **Step 2: Add anterior/siguiente navigation at the end of `art-content`**

In `resources/views/blog/show.blade.php`, find the closing `</main>` tag of `.art-content` and insert before it:

```blade
{{-- Anterior / Siguiente en la serie --}}
@if(isset($anterior) || isset($siguiente))
<nav style="border-top:1px solid #f3f4f6;margin-top:2rem;padding-top:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem" aria-label="Navegación de la serie">
  <div>
    @if($anterior)
    <a href="/blog/{{ $anterior->slug }}"
       style="display:block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:.85rem 1rem;text-decoration:none;color:#374151">
      <span style="font-size:.72rem;font-weight:600;color:#9ca3af;display:block;margin-bottom:.3rem">← Anterior</span>
      <span style="font-size:.85rem;font-weight:600;color:#1f2937;line-height:1.35;display:block">{{ Str::limit($anterior->titulo, 55) }}</span>
    </a>
    @endif
  </div>
  <div style="text-align:right">
    @if($siguiente)
    <a href="/blog/{{ $siguiente->slug }}"
       style="display:block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:.85rem 1rem;text-decoration:none;color:#374151">
      <span style="font-size:.72rem;font-weight:600;color:#9ca3af;display:block;margin-bottom:.3rem">Siguiente →</span>
      <span style="font-size:.85rem;font-weight:600;color:#1f2937;line-height:1.35;display:block">{{ Str::limit($siguiente->titulo, 55) }}</span>
    </a>
    @endif
  </div>
</nav>
@endif
```

- [ ] **Step 3: Test manually**

- Visit an article that's part of a series: the purple banner "Serie: X — Parte Y de Z" appears between the hero and the article body.
- The anterior/siguiente nav block shows at the bottom of the article.
- Article 1 of 3: only "Siguiente →" visible.
- Article 3 of 3: only "← Anterior" visible.
- Non-serie article: no banner, no nav.

- [ ] **Step 4: Commit**

```bash
git add resources/views/blog/show.blade.php
git commit -m "feat: series banner and anterior/siguiente nav in blog article view"
```

---

### Task 15: `AiInternalLinker` + `AiArticleService` — series integration

**Files:**
- Modify: `app/Services/AI/AiInternalLinker.php`
- Modify: `app/Services/AI/AiArticleService.php`
- Modify: `resources/views/admin/articulos/_ai_panel.blade.php`
- Create: `tests/Unit/AiInternalLinkerSerieTest.php`

When generating an article that belongs to a serie, the linker forces links to previously published articles of the same serie (articles with `orden_en_serie < current article's orden`).

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/AiInternalLinkerSerieTest.php
namespace Tests\Unit;

use App\Models\Serie;
use App\Services\AI\AiInternalLinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiInternalLinkerSerieTest extends TestCase
{
    use RefreshDatabase;

    public function test_forced_ids_are_returned_first(): void
    {
        $serie = Serie::create(['nombre' => 'Test', 'slug' => 'ts']);

        // Art 1 and Art 2 are published and part of the serie
        \DB::table('articulos')->insert([
            ['titulo' => 'Art 1', 'slug' => 'art-1', 'estado' => 'publicado',
             'schema_type' => 'BlogPosting', 'ai_context_summary' => 'Resume 1',
             'focus_keyword' => 'keyword1', 'serie_id' => $serie->id, 'orden_en_serie' => 1,
             'fecha_publicacion' => now()->subDays(10), 'created_at' => now(), 'updated_at' => now()],
            ['titulo' => 'Art 2', 'slug' => 'art-2', 'estado' => 'publicado',
             'schema_type' => 'BlogPosting', 'ai_context_summary' => 'Resume 2',
             'focus_keyword' => 'keyword2', 'serie_id' => $serie->id, 'orden_en_serie' => 2,
             'fecha_publicacion' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now()],
            // Unrelated published article
            ['titulo' => 'Other', 'slug' => 'other', 'estado' => 'publicado',
             'schema_type' => 'BlogPosting', 'ai_context_summary' => 'keyword1 keyword2 keyword3',
             'focus_keyword' => 'keyword1', 'serie_id' => null,
             'fecha_publicacion' => now()->subDays(1), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $art1Id = \DB::table('articulos')->where('slug', 'art-1')->value('id');
        $art2Id = \DB::table('articulos')->where('slug', 'art-2')->value('id');

        $linker = new AiInternalLinker();
        $result = $linker->findRelated('keyword1 keyword2', null, 5, [$art1Id, $art2Id]);

        // First two results must be the forced ones
        $this->assertEquals('art-1', $result[0]['slug']);
        $this->assertEquals('art-2', $result[1]['slug']);
    }

    public function test_without_forced_ids_works_as_before(): void
    {
        \DB::table('articulos')->insert([
            'titulo' => 'Art', 'slug' => 'art-x', 'estado' => 'publicado',
            'schema_type' => 'BlogPosting', 'ai_context_summary' => 'test keyword',
            'focus_keyword' => 'test', 'fecha_publicacion' => now()->subDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $linker = new AiInternalLinker();
        $result = $linker->findRelated('test keyword', null, 5);
        $this->assertCount(1, $result);
        $this->assertEquals('art-x', $result[0]['slug']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter AiInternalLinkerSerieTest`
Expected: FAIL — `findRelated` doesn't accept 4th parameter

- [ ] **Step 3: Update `AiInternalLinker::findRelated()`**

```php
// app/Services/AI/AiInternalLinker.php
// Replace the entire findRelated() method:

public function findRelated(string $keyword, ?int $categoriaId, int $limit, array $forcedIds = []): array
{
    // --- Forced articles (previous serie articles) ---
    $forced = [];
    if (!empty($forcedIds)) {
        $forced = Articulo::publicados()
            ->whereIn('id', $forcedIds)
            ->select('id', 'titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
            ->orderByDesc('fecha_publicacion')
            ->get()
            ->map(fn ($a) => [
                'titulo'             => $a->titulo,
                'slug'               => $a->slug,
                'focus_keyword'      => $a->focus_keyword,
                'ai_context_summary' => $a->ai_context_summary,
            ])
            ->toArray();
    }

    $remaining = max(0, $limit - count($forced));
    if ($remaining === 0) {
        return $forced;
    }

    // --- Keyword-scored articles (excluding forced IDs) ---
    $articulos = Articulo::publicados()
        ->whereNotIn('id', $forcedIds)
        ->whereNotNull('ai_context_summary')
        ->select('id', 'titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
        ->orderByDesc('fecha_publicacion')
        ->get();

    if ($articulos->isEmpty()) {
        return $forced;
    }

    $keywords = array_filter(explode(' ', mb_strtolower($keyword)));

    $byKeyword = $articulos
        ->map(function ($a) use ($keywords, $categoriaId) {
            $text  = mb_strtolower($a->titulo . ' ' . $a->focus_keyword . ' ' . $a->ai_context_summary);
            $score = array_sum(array_map(fn ($k) => substr_count($text, $k), $keywords));
            if ($categoriaId && $a->categoria_blog_id === $categoriaId) {
                $score += 3;
            }
            return ['articulo' => $a, 'score' => $score];
        })
        ->filter(fn ($item) => $item['score'] > 0)
        ->sortByDesc('score')
        ->take($remaining)
        ->pluck('articulo')
        ->map(fn ($a) => [
            'titulo'             => $a->titulo,
            'slug'               => $a->slug,
            'focus_keyword'      => $a->focus_keyword,
            'ai_context_summary' => $a->ai_context_summary,
        ])
        ->values()
        ->toArray();

    return array_merge($forced, $byKeyword);
}
```

- [ ] **Step 4: Update `AiArticleService::generate()` to pass serie context**

In `app/Services/AI/AiArticleService.php`, replace the section that calls `$this->linker->findRelated(...)` (lines ~21-25):

```php
// 1. Contexto de artículos anteriores (con forzado de artículos de la serie)
$forcedIds = [];
if (!empty($input['serie_id']) && !empty($input['orden_en_serie'])) {
    $forcedIds = Articulo::where('serie_id', $input['serie_id'])
        ->where('orden_en_serie', '<', (int) $input['orden_en_serie'])
        ->publicados()
        ->pluck('id')
        ->toArray();
}

$related = $this->linker->findRelated(
    $input['focus_keyword'] ?? $input['idea'] ?? '',
    $input['categoria_id'] ?? null,
    $settings->max_articles_context,
    $forcedIds
);
$relatedContext = $this->linker->formatForPrompt($related);
```

- [ ] **Step 5: Pass serie context from the AI panel JS**

In `resources/views/admin/articulos/_ai_panel.blade.php`, find the `payload` object inside the `ai-generate-btn` click handler (around line 150) and add `serie_id` and `orden_en_serie`:

```javascript
// Find the payload object and add at the end:
const payload = {
    idea,
    focus_keyword:  document.getElementById('ai-keyword').value,
    localidad:      document.getElementById('ai-localidad').value,
    tono:           document.getElementById('ai-tono').value,
    instrucciones:  document.getElementById('ai-instrucciones').value,
    categoria_id:   parseInt(document.getElementById('categoria_blog_id')?.value) || null,
    generate_image: document.getElementById('ai-img').checked,
    generate_faq:   document.getElementById('ai-faq').checked,
    suggest_links:  document.getElementById('ai-links').checked,
    // Serie context (populated if article belongs to a serie)
    serie_id:        parseInt(document.getElementById('serie_id')?.value) || null,
    orden_en_serie:  parseInt(document.getElementById('orden_en_serie')?.value) || null,
};
```

Also add `serie_id` and `orden_en_serie` to the `AiGenerateController::generate()` validation:

In `app/Http/Controllers/Admin/AiGenerateController.php`, add to the `$input = $request->validate([...])` array:
```php
'serie_id'      => ['nullable', 'integer', 'exists:series,id'],
'orden_en_serie'=> ['nullable', 'integer', 'min:1'],
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter AiInternalLinkerSerieTest`
Expected: PASS (2 tests)

Run full suite: `php artisan test`
Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Services/AI/AiInternalLinker.php app/Services/AI/AiArticleService.php resources/views/admin/articulos/_ai_panel.blade.php app/Http/Controllers/Admin/AiGenerateController.php tests/Unit/AiInternalLinkerSerieTest.php
git commit -m "feat: AiInternalLinker forced IDs for serie articles + AiArticleService serie context"
```

---

## Full test suite

Run after completing all tasks:

```bash
php artisan test
```

Expected: All test classes pass:
- `SeriesTableTest` (2 tests)
- `ArticulosSerieFieldsTest` (3 tests)
- `SerieModelTest` (3 tests)
- `RouteExistsTest` (5 tests)
- `SerieControllerTest` (5 tests)
- `CalendarioControllerTest` (7 tests)
- `ArticuloSerieFieldsTest` (4 tests)
- `AdminNavTest` (1 test)
- `NewsletterTest` (3 tests)
- `PublicarArticulosProgramadosTest` (5 tests)
- `BlogSerieTest` (5 tests)
- `AiInternalLinkerSerieTest` (2 tests)

**Total: 45 tests**

After the full suite passes, cross-check against spec section 11 manual verification checklists (11.2–11.9) to confirm the UI and end-to-end flows work correctly.

