<?php

namespace App\Console\Commands;

use App\Models\Articulo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RenombrarImagenesArticulos extends Command
{
    protected $signature = 'articulos:renombrar-imagenes
                            {--dry-run : Muestra los cambios sin aplicarlos}
                            {--id=     : Procesar solo el artículo con este ID}';

    protected $description = 'Renombra las imágenes locales de artículos a nombres SEO-friendly y actualiza la BD';

    // Dominios de las instalaciones conocidas (local y producción)
    private const LOCAL_HOSTS = [
        'localhost',
        '127.0.0.1',
        'www.eventify.es',
        'eventify.es',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $filterId = $this->option('id');

        if ($dryRun) {
            $this->warn('⚠  MODO DRY-RUN — no se modifica nada.');
        }

        $query = Articulo::whereNotNull('imagen_principal');
        if ($filterId) {
            $query->where('id', (int) $filterId);
        }
        $articulos = $query->get(['id', 'slug', 'focus_keyword', 'titulo', 'imagen_principal']);

        $this->info("Artículos con imagen: {$articulos->count()}");
        $this->newLine();

        $renombrados = 0;
        $saltados    = 0;
        $errores     = 0;

        foreach ($articulos as $articulo) {
            $url = $articulo->imagen_principal;

            // Saltar URLs externas (Unsplash, CDNs, etc.)
            if (! $this->isLocalUrl($url)) {
                $this->line("  <fg=gray>SKIP  [{$articulo->id}] URL externa: {$url}</>");
                $saltados++;
                continue;
            }

            // Resolver ruta física y disco
            [$disk, $relPath, $isStorage] = $this->resolveFile($url);

            if ($relPath === null) {
                $this->line("  <fg=yellow>SKIP  [{$articulo->id}] No se pudo resolver la ruta: {$url}</>");
                $saltados++;
                continue;
            }

            // Comprobar que el fichero existe
            $exists = $isStorage
                ? Storage::disk('public')->exists($relPath)
                : file_exists(public_path($relPath));

            if (! $exists) {
                $this->line("  <fg=yellow>WARN  [{$articulo->id}] Fichero no encontrado en disco: {$relPath}</>");
                $saltados++;
                continue;
            }

            // Construir el nombre SEO
            $dir         = dirname($relPath);   // p.ej. "articulos" o "uploads/blog"
            $ext         = strtolower(pathinfo($relPath, PATHINFO_EXTENSION)) ?: 'jpg';
            $currentName = pathinfo($relPath, PATHINFO_FILENAME);
            $seoBase     = $this->buildSeoName($articulo);

            if (! $seoBase) {
                $this->line("  <fg=yellow>SKIP  [{$articulo->id}] No hay slug/keyword/título para construir nombre</>");
                $saltados++;
                continue;
            }

            // Si el nombre ya es SEO-friendly, saltar
            if ($currentName === $seoBase) {
                $this->line("  <fg=gray>OK    [{$articulo->id}] Ya tiene nombre correcto: {$currentName}.{$ext}</>");
                $saltados++;
                continue;
            }

            // Elegir nombre final sin colisiones
            $newName = $this->uniqueName($dir, $seoBase, $ext, $isStorage, $currentName);
            $newPath = $dir . '/' . $newName . '.' . $ext;

            $this->line("  <fg=cyan>RENAME [{$articulo->id}]</> {$currentName}.{$ext}  →  {$newName}.{$ext}");

            if ($dryRun) {
                $renombrados++;
                continue;
            }

            // Renombrar en disco
            try {
                if ($isStorage) {
                    Storage::disk('public')->move($relPath, $newPath);
                } else {
                    rename(public_path($relPath), public_path($newPath));
                }
            } catch (\Throwable $e) {
                $this->error("  ERROR al renombrar fichero: {$e->getMessage()}");
                $errores++;
                continue;
            }

            // Actualizar URL en la BD
            $newUrl = $isStorage
                ? Storage::disk('public')->url($newPath)
                : url($newPath);

            $articulo->update(['imagen_principal' => $newUrl]);
            $renombrados++;
        }

        $this->newLine();
        $this->info("Renombrados: {$renombrados}  |  Saltados: {$saltados}  |  Errores: {$errores}");

        if ($dryRun && $renombrados > 0) {
            $this->warn('Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return self::SUCCESS;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function isLocalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        foreach (self::LOCAL_HOSTS as $local) {
            if (str_contains($host, $local)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Devuelve [disk_name, relative_path, is_storage_disk]
     * - is_storage_disk = true  → usa Storage::disk('public'), ruta relativa desde storage/app/public/
     * - is_storage_disk = false → ruta relativa desde public/
     */
    private function resolveFile(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        // Quitar el base path si hay subfolder en local (p.ej. /web-publica-eventify/public)
        // Normalizar quitando dobles barras
        $path = preg_replace('#/+#', '/', $path);

        // storage/articulos/  →  Storage::disk('public')
        if (preg_match('#/storage/(.+)$#', $path, $m)) {
            return ['public', $m[1], true];
        }

        // uploads/blog/  →  public/uploads/blog/
        if (preg_match('#/(uploads/.+)$#', $path, $m)) {
            return ['public_dir', $m[1], false];
        }

        return [null, null, false];
    }

    private function buildSeoName(Articulo $articulo): string
    {
        $base = $articulo->focus_keyword
             ?: $articulo->slug
             ?: $articulo->titulo
             ?: '';

        $slug = Str::slug($base);
        $slug = substr($slug, 0, 70);

        return $slug ? $slug . '-imagen-principal' : '';
    }

    private function uniqueName(string $dir, string $base, string $ext, bool $isStorage, string $currentName): string
    {
        $candidate = $base;
        $i = 1;

        while (true) {
            $relPath = $dir . '/' . $candidate . '.' . $ext;

            // Si el candidato coincide con el fichero actual, está bien (rename in-place)
            if ($candidate === $currentName) {
                return $candidate;
            }

            $taken = $isStorage
                ? Storage::disk('public')->exists($relPath)
                : file_exists(public_path($relPath));

            if (! $taken) {
                return $candidate;
            }

            $candidate = $base . '-' . $i;
            $i++;
        }
    }
}
