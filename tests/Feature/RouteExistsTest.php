<?php
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
