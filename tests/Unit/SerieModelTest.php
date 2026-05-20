<?php
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
