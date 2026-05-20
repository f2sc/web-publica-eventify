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
