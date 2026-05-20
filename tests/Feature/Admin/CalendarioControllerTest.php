<?php
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
        // Start 2026-06-03 (Wednesday). First article gets start date, second gets next Saturday (dow=6) = 2026-06-06
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
