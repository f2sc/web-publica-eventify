<?php
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
