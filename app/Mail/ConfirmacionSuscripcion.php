<?php

namespace App\Mail;

use App\Models\Suscriptor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacionSuscripcion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Suscriptor $suscriptor) {}

    public function build()
    {
        return $this->subject('Confirma tu suscripción al blog de Eventify')
                    ->view('emails.confirmacion-suscripcion');
    }
}
