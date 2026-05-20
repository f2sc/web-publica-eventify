<?php
namespace Tests\Feature;

use App\Jobs\EnviarNewsletterArticulo;
use App\Mail\ArticuloPublicado;
use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticulo(): Articulo
    {
        $id = \DB::table('articulos')->insertGetId([
            'titulo' => 'Nuevo artículo', 'slug' => 'nuevo-art', 'estado' => 'publicado',
            'schema_type' => 'BlogPosting', 'extracto' => 'Un extracto breve.',
            'fecha_publicacion' => now(), 'enviar_newsletter' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return Articulo::find($id);
    }

    private function makeSuscriptor(bool $confirmado = true, string $email = 'test@example.com', string $token = 'tok123'): Suscriptor
    {
        return Suscriptor::create([
            'nombre' => 'Test', 'email' => $email,
            'token_confirmacion' => $token, 'confirmado' => $confirmado,
        ]);
    }

    public function test_job_sends_mail_to_confirmed_suscriptores(): void
    {
        Mail::fake();
        $art = $this->makeArticulo();
        $this->makeSuscriptor(true, 'test@example.com', 'tok123');
        $this->makeSuscriptor(false, 'unconfirmed@example.com', 'tok-unconfirmed'); // unconfirmed — should NOT receive

        dispatch(new EnviarNewsletterArticulo($art));

        Mail::assertSent(ArticuloPublicado::class, 1);
        Mail::assertSent(ArticuloPublicado::class, fn ($m) => $m->hasTo('test@example.com'));
    }

    public function test_job_does_not_send_to_unsubscribed(): void
    {
        Mail::fake();
        $art = $this->makeArticulo();
        Suscriptor::create([
            'nombre' => 'Dado de baja', 'email' => 'baja@example.com',
            'token_confirmacion' => 'tok456', 'confirmado' => true,
            'unsubscribed_at' => now(),
        ]);
        dispatch(new EnviarNewsletterArticulo($art));
        Mail::assertNothingSent();
    }

    public function test_mailable_renders_article_data(): void
    {
        $art = $this->makeArticulo();
        $sus = $this->makeSuscriptor();
        $mail = new ArticuloPublicado($art, $sus);
        $rendered = $mail->build();
        $this->assertNotNull($rendered);
    }
}
