<?php
namespace App\Mail;

use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArticuloPublicado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Articulo   $articulo,
        public Suscriptor $suscriptor
    ) {}

    public function build(): static
    {
        return $this->subject('Nuevo artículo en el blog de Eventify: ' . $this->articulo->titulo)
                    ->view('emails.articulo-publicado');
    }
}
