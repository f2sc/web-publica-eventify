<?php
namespace App\Console\Commands;

use App\Jobs\EnviarNewsletterArticulo;
use App\Models\Articulo;
use Illuminate\Console\Command;

class PublicarArticulosProgramados extends Command
{
    protected $signature   = 'app:publicar-articulos-programados';
    protected $description = 'Publica artículos con estado=programado cuya fecha_publicacion ya ha pasado';

    public function handle(): int
    {
        $articulos = Articulo::where('estado', 'programado')
            ->where('fecha_publicacion', '<=', now())
            ->get();

        foreach ($articulos as $articulo) {
            $articulo->update(['estado' => 'publicado']);
            $this->line("Publicado: [{$articulo->id}] {$articulo->titulo}");

            if ($articulo->enviar_newsletter) {
                dispatch(new EnviarNewsletterArticulo($articulo));
            }
        }

        $this->info("Procesados: {$articulos->count()} artículos.");
        return self::SUCCESS;
    }
}
