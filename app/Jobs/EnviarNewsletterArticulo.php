<?php
namespace App\Jobs;

use App\Mail\ArticuloPublicado;
use App\Models\Articulo;
use App\Models\Suscriptor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarNewsletterArticulo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Articulo $articulo) {}

    public function handle(): void
    {
        $suscriptores = Suscriptor::where('confirmado', true)
            ->whereNull('unsubscribed_at')
            ->get();

        foreach ($suscriptores as $suscriptor) {
            Mail::to($suscriptor->email)
                ->send(new ArticuloPublicado($this->articulo, $suscriptor));
        }
    }
}
