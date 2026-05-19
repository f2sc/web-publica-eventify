<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class IaConfigController extends Controller
{
    public function edit()
    {
        $config = AiSetting::instance();
        return view('admin.ia.config', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_provider'        => ['required', 'in:claude,openai'],
            'text_model'           => ['required', 'string', 'max:100'],
            'text_api_key'         => ['nullable', 'string', 'max:500'],
            'image_provider'       => ['required', 'in:google,openai'],
            'image_model'          => ['required', 'string', 'max:100'],
            'image_api_key'        => ['nullable', 'string', 'max:500'],
            'image_size'           => ['required', 'string'],
            'image_style'          => ['nullable', 'string', 'max:50'],
            'prompt_system'        => ['nullable', 'string'],
            'prompt_image'         => ['nullable', 'string'],
            'prompt_interlinking'  => ['nullable', 'string'],
            'default_tone'         => ['required', 'string', 'max:100'],
            'default_language'     => ['required', 'string', 'max:10'],
            'default_length'       => ['required', 'string', 'max:50'],
            'max_articles_context' => ['required', 'integer', 'min:1', 'max:20'],
            'max_internal_links'   => ['required', 'integer', 'min:0', 'max:10'],
            'auto_generate_image'  => ['boolean'],
            'auto_generate_faq'    => ['boolean'],
            'always_draft'         => ['boolean'],
        ]);

        $config = AiSetting::instance();

        // Solo actualizar API keys si se proporcionaron nuevos valores
        if (empty($data['text_api_key'])) {
            unset($data['text_api_key']);
        }
        if (empty($data['image_api_key'])) {
            unset($data['image_api_key']);
        }

        $data['auto_generate_image'] = $request->boolean('auto_generate_image');
        $data['auto_generate_faq']   = $request->boolean('auto_generate_faq');
        $data['always_draft']        = $request->boolean('always_draft');

        $config->update($data);

        return redirect()->route('admin.ia.config')->with('success', 'Configuración de IA guardada.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'     => ['required', 'in:text,image'],
            'provider' => ['required', 'in:claude,openai,google'],
            'api_key'  => ['nullable', 'string', 'max:500'],
            'model'    => ['nullable', 'string', 'max:100'],
        ]);

        $config   = AiSetting::instance();
        $type     = $data['type'];
        $provider = $data['provider'];
        $apiKey   = !empty($data['api_key'])
            ? $data['api_key']
            : ($type === 'text' ? $config->text_api_key : $config->image_api_key);
        $model    = !empty($data['model'])
            ? $data['model']
            : ($type === 'text' ? $config->text_model : $config->image_model);

        if (!$apiKey) {
            return response()->json(['ok' => false, 'message' => 'No hay API key configurada. Introdúcela en el campo y prueba de nuevo.']);
        }

        try {
            if ($type === 'text' && $provider === 'claude') {
                $response = Http::timeout(15)->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $model ?: 'claude-haiku-4-5-20251001',
                    'max_tokens' => 5,
                    'messages'   => [['role' => 'user', 'content' => 'di "ok"']],
                ]);

                if ($response->successful()) {
                    $reply = $response->json('content.0.text') ?? '...';
                    return response()->json(['ok' => true, 'message' => "✓ Conectado — modelo: {$model} — respuesta: \"{$reply}\""]);
                }
                $err = $response->json('error.message') ?? $response->body();
                \Log::error("IA test [claude/text] {$response->status()}", ['body' => $response->body()]);
                return response()->json(['ok' => false, 'message' => "Error {$response->status()}: {$err}"]);
            }

            if ($type === 'text' && $provider === 'openai') {
                $response = Http::timeout(15)->withToken($apiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'      => $model ?: 'gpt-4o-mini',
                        'max_tokens' => 5,
                        'messages'   => [['role' => 'user', 'content' => 'say "ok"']],
                    ]);

                if ($response->successful()) {
                    $reply = $response->json('choices.0.message.content') ?? '...';
                    return response()->json(['ok' => true, 'message' => "✓ Conectado — modelo: {$model} — respuesta: \"{$reply}\""]);
                }
                $err = $response->json('error.message') ?? $response->body();
                \Log::error("IA test [openai/text] {$response->status()}", ['body' => $response->body()]);
                return response()->json(['ok' => false, 'message' => "Error {$response->status()}: {$err}"]);
            }

            if ($type === 'image' && $provider === 'google') {
                $response = Http::timeout(15)
                    ->get('https://generativelanguage.googleapis.com/v1beta/models', ['key' => $apiKey]);

                if ($response->successful()) {
                    $names = collect($response->json('models', []))
                        ->pluck('name')->filter(fn($n) => str_contains($n, 'imagen'))->take(3)->implode(', ');
                    $names = $names ?: 'modelos disponibles';
                    return response()->json(['ok' => true, 'message' => "✓ Conectado con Google AI — {$names}"]);
                }
                $err = $response->json('error.message') ?? $response->body();
                \Log::error("IA test [google/image] {$response->status()}", ['body' => $response->body()]);
                return response()->json(['ok' => false, 'message' => "Error {$response->status()}: {$err}"]);
            }

            if ($type === 'image' && $provider === 'openai') {
                $response = Http::timeout(15)->withToken($apiKey)
                    ->get('https://api.openai.com/v1/models');

                if ($response->successful()) {
                    return response()->json(['ok' => true, 'message' => '✓ Conectado con OpenAI — API key válida.']);
                }
                $err = $response->json('error.message') ?? $response->body();
                \Log::error("IA test [openai/image] {$response->status()}", ['body' => $response->body()]);
                return response()->json(['ok' => false, 'message' => "Error {$response->status()}: {$err}"]);
            }

            return response()->json(['ok' => false, 'message' => 'Combinación provider/type no reconocida.']);

        } catch (Throwable $e) {
            \Log::error("IA test [{$provider}/{$type}] excepción", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['ok' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
        }
    }

    public function fetchModels(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'     => ['required', 'in:text,image'],
            'provider' => ['required', 'in:claude,openai,google'],
            'api_key'  => ['nullable', 'string', 'max:500'],
        ]);

        $config   = AiSetting::instance();
        $type     = $data['type'];
        $provider = $data['provider'];
        $apiKey   = !empty($data['api_key'])
            ? $data['api_key']
            : ($type === 'text' ? $config->text_api_key : $config->image_api_key);

        if (!$apiKey) {
            return response()->json(['ok' => false, 'message' => 'Sin API key. Introdúcela y vuelve a intentarlo.', 'models' => []]);
        }

        try {
            $models = match (true) {
                $provider === 'claude'                    => $this->fetchClaudeModels($apiKey),
                $provider === 'openai' && $type === 'text'  => $this->fetchOpenAiTextModels($apiKey),
                $provider === 'openai' && $type === 'image' => $this->fetchOpenAiImageModels($apiKey),
                $provider === 'google'                    => $this->fetchGoogleImageModels($apiKey),
                default => [],
            };

            return response()->json(['ok' => true, 'models' => $models]);

        } catch (Throwable $e) {
            \Log::error("IA fetch-models [{$provider}/{$type}]", ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'models' => []]);
        }
    }

    private function fetchClaudeModels(string $apiKey): array
    {
        $response = Http::timeout(10)->withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->get('https://api.anthropic.com/v1/models');

        if ($response->failed()) {
            throw new RuntimeException('Error ' . $response->status() . ': ' . ($response->json('error.message') ?? $response->body()));
        }

        return collect($response->json('data', []))
            ->pluck('id')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    private function fetchOpenAiTextModels(string $apiKey): array
    {
        $response = Http::timeout(10)->withToken($apiKey)->get('https://api.openai.com/v1/models');

        if ($response->failed()) {
            throw new RuntimeException('Error ' . $response->status() . ': ' . ($response->json('error.message') ?? $response->body()));
        }

        return collect($response->json('data', []))
            ->pluck('id')
            ->filter(function ($id) {
                return preg_match('/^(gpt-|o1|o3|o4)/', $id)
                    && ! str_contains($id, 'instruct')
                    && ! str_contains($id, 'realtime')
                    && ! str_starts_with($id, 'gpt-3.5-turbo-instruct');
            })
            ->sort()
            ->values()
            ->toArray();
    }

    private function fetchOpenAiImageModels(string $apiKey): array
    {
        $known = ['gpt-image-2', 'dall-e-3', 'dall-e-2'];

        $response = Http::timeout(10)->withToken($apiKey)->get('https://api.openai.com/v1/models');

        if ($response->failed()) {
            // Clave inválida → lanzar error; si es restricción de proyecto devolvemos la lista conocida
            if ($response->status() === 401) {
                throw new RuntimeException('Error 401: API key inválida.');
            }
            return $known;
        }

        $fromApi = collect($response->json('data', []))
            ->pluck('id')
            ->filter(fn($id) => str_starts_with($id, 'dall-e') || str_starts_with($id, 'gpt-image'))
            ->sort()
            ->values()
            ->toArray();

        // Las claves de proyecto con permisos limitados no devuelven dall-e en /v1/models
        return $fromApi ?: $known;
    }

    private function fetchGoogleImageModels(string $apiKey): array
    {
        $response = Http::timeout(10)
            ->get('https://generativelanguage.googleapis.com/v1beta/models', ['key' => $apiKey]);

        if ($response->failed()) {
            throw new RuntimeException('Error ' . $response->status() . ': ' . ($response->json('error.message') ?? $response->body()));
        }

        return collect($response->json('models', []))
            ->filter(fn($m) => str_contains($m['name'] ?? '', 'imagen'))
            ->map(fn($m) => str_replace('models/', '', $m['name']))
            ->sort()
            ->values()
            ->toArray();
    }
}
