<?php
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
