<?php
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
            'titulo'            => 'No newsletter',
            'enviar_newsletter' => '0',
        ]))->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'No newsletter', 'enviar_newsletter' => false]);
    }

    public function test_datetime_local_fecha_validacion(): void
    {
        $this->withSession($this->auth)->post('/admin/articulos', $this->basePayload([
            'titulo'            => 'Con hora',
            'fecha_publicacion' => '2026-06-15T10:30',
        ]))->assertRedirect();
        $this->assertDatabaseHas('articulos', ['titulo' => 'Con hora']);
        $art = \App\Models\Articulo::where('titulo', 'Con hora')->first();
        $this->assertEquals('10:30', $art->fecha_publicacion->format('H:i'));
    }
}
