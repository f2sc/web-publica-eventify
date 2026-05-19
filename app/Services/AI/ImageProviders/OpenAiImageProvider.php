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

    public function generate(string $prompt, string $size = '1024x1024'): string
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

        $filename = 'articulos/' . Str::uuid() . '.jpg';

        // gpt-image-2 devuelve b64_json; DALL-E 3 devuelve url
        $b64 = $response->json('data.0.b64_json') ?? null;
        if ($b64) {
            Storage::disk('public')->put($filename, base64_decode($b64));
        } else {
            $url = $response->json('data.0.url') ?? null;
            if (!$url) {
                \Log::error('OpenAI Images respuesta inesperada', ['body' => $response->body()]);
                throw new RuntimeException('OpenAI Images no devolvió imagen.');
            }
            $bytes = Http::timeout(30)->get($url)->body();
            Storage::disk('public')->put($filename, $bytes);
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
}
