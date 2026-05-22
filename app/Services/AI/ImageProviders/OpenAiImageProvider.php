<?php

namespace App\Services\AI\ImageProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiImageProvider implements AiImageProviderInterface
{
    private float $lastCost = 0.0;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'dall-e-3'
    ) {}

    public function generate(string $prompt, string $size = '1024x1024', string $baseName = ''): string
    {
        $payload = [
            'model'  => $this->model,
            'prompt' => $prompt,
            'n'      => 1,
        ];

        // gpt-image-2 no acepta response_format ni size con los valores de DALL-E
        if (str_starts_with($this->model, 'dall-e')) {
            $payload['size']            = $size;
            $payload['response_format'] = 'b64_json';
        }

        $response = Http::timeout(180)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/images/generations', $payload);

        if ($response->failed()) {
            $msg = $response->json('error.message') ?? $response->body();
            \Log::error('OpenAI Images error', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->status() === 400 && str_contains($msg, 'does not exist')) {
                throw new RuntimeException(
                    "La clave OpenAI (sk-proj-...) no tiene permiso de generación de imágenes. " .
                    "Ve a platform.openai.com → tu proyecto → Permissions y habilita 'Images', " .
                    "o usa una clave API clásica (sk-...) sin restricciones de proyecto."
                );
            }

            throw new RuntimeException('OpenAI Images error ' . $response->status() . ': ' . $msg);
        }

        $filename = 'articulos/' . self::buildFilename($baseName) . '.jpg';

        // gpt-image-2 devuelve b64_json; DALL-E 3 devuelve url
        $b64 = $response->json('data.0.b64_json') ?? null;
        if ($b64) {
            $bytes = base64_decode($b64);
            if (!$bytes || strlen($bytes) < 100) {
                throw new RuntimeException('OpenAI devolvió base64 vacío o inválido.');
            }
            $saved = Storage::disk('public')->put($filename, $bytes);
        } else {
            $remoteUrl = $response->json('data.0.url') ?? null;
            if (!$remoteUrl) {
                \Log::error('OpenAI Images respuesta inesperada', ['body' => $response->body()]);
                throw new RuntimeException('OpenAI Images no devolvió imagen ni URL.');
            }
            $dlResp = Http::timeout(30)->get($remoteUrl);
            if ($dlResp->failed()) {
                throw new RuntimeException('No se pudo descargar la imagen desde OpenAI (HTTP ' . $dlResp->status() . ').');
            }
            $bytes = $dlResp->body();
            if (!$bytes || strlen($bytes) < 100) {
                throw new RuntimeException('La imagen descargada de OpenAI está vacía.');
            }
            $saved = Storage::disk('public')->put($filename, $bytes);
        }

        if (!$saved) {
            \Log::error('OpenAI Images: no se pudo escribir en disco', ['filename' => $filename, 'disk_path' => storage_path('app/public')]);
            throw new RuntimeException('No se pudo guardar la imagen en el servidor. Revisa los permisos del directorio storage/app/public/articulos/');
        }

        $this->lastCost = 0.04;

        return Storage::disk('public')->url($filename);
    }

    public function lastCost(): float
    {
        return $this->lastCost;
    }

    public function providerName(): string
    {
        return 'openai';
    }

    public function modelName(): string
    {
        return $this->model;
    }

    public static function buildFilename(string $baseName): string
    {
        if ($baseName !== '') {
            // Sanitizar: slug limpio, máximo 80 chars
            $slug = \Illuminate\Support\Str::slug($baseName);
            $slug = substr($slug, 0, 80);
            if ($slug !== '') {
                return $slug;
            }
        }
        return (string) \Illuminate\Support\Str::uuid();
    }
}
