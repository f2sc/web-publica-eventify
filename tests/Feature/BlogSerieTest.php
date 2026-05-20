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
