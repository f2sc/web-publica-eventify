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
