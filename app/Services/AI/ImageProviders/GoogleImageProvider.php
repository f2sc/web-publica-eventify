<?php

namespace App\Services\AI\ImageProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleImageProvider implements AiImageProviderInterface
{
    private float $lastCost = 0.0;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'imagen-4.0-flash-exp'
    ) {}

    public function generate(string $prompt, string $size = '1024x1024', string $baseName = ''): string
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateImages?key={$this->apiKey}", [
                'prompt'           => $prompt,
                'number_of_images' => 1,
                'aspect_ratio'     => '1:1',
            ]);

        if ($response->failed()) {
            \Log::error('Google Imagen error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Error Google Imagen API: ' . $response->status() . ' — ' . $response->body());
        }

        $data = $response->json();
        $b64  = $data['generatedImages'][0]['image']['imageBytes']
             ?? $data['predictions'][0]['bytesBase64Encoded']
             ?? null;

        if (!$b64) {
            \Log::error('Google Imagen respuesta inesperada', ['body' => $response->body()]);
            throw new RuntimeException('Google Imagen no devolvió imagen: ' . json_encode($data));
        }

        $bytes = base64_decode($b64);
        if (!$bytes || strlen($bytes) < 100) {
            throw new RuntimeException('Google Imagen devolvió base64 vacío o inválido.');
        }

        $filename = 'articulos/' . OpenAiImageProvider::buildFilename($baseName) . '.jpg';
        $saved    = Storage::disk('public')->put($filename, $bytes);

        if (!$saved) {
            \Log::error('Google Imagen: no se pudo escribir en disco', ['filename' => $filename, 'disk_path' => storage_path('app/public')]);
            throw new RuntimeException('No se pudo guardar la imagen en el servidor. Revisa los permisos del directorio storage/app/public/articulos/');
        }

        $this->lastCost = 0.02;

        return Storage::disk('public')->url($filename);
    }

    public function lastCost(): float
    {
        return $this->lastCost;
    }

    public function providerName(): string
    {
        return 'google';
    }

    public function modelName(): string
    {
        return $this->model;
    }
}
