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
             'focus_keyword' => 'keyword1', 'serie_id' => null, 'orden_en_serie' => null,
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
