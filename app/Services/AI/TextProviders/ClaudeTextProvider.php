<?php

namespace App\Services\AI\TextProviders;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeTextProvider implements AiTextProviderInterface
{
    private array $lastUsage = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-sonnet-4-6'
    ) {}

    public function generateArticle(string $userPrompt, string $systemPrompt): array
    {
        $response = $this->callWithTool($systemPrompt, $userPrompt);

        $this->lastUsage = [
            'input_tokens'  => $response['usage']['input_tokens'] ?? 0,
            'output_tokens' => $response['usage']['output_tokens'] ?? 0,
        ];

        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'tool_use' && ($block['name'] ?? '') === 'create_article') {
                return $block['input'];
            }
        }

        // Fallback: intentar parsear texto libre si tool_use no llegó
        $text = $response['content'][0]['text'] ?? '';
        $json = $this->extractJson($text);
        if ($json === null) {
            throw new RuntimeException('Claude no devolvió JSON válido: ' . substr($text, 0, 300));
        }
        return $json;
    }

    public function regenerateField(string $field, array $context, string $systemPrompt): string
    {
        $fieldPrompts = [
            'titulo'            => "Genera un título SEO atractivo (máx. 70 chars) para el artículo. Devuelve SOLO el texto del título, sin comillas.",
            'extracto'          => "Genera un extracto de 1-2 frases (máx. 160 chars) que resuma el artículo. Devuelve SOLO el texto.",
            'meta_title'        => "Genera un meta title SEO (máx. 60 chars). Devuelve SOLO el texto.",
            'meta_description'  => "Genera una meta description SEO (120-160 chars). Devuelve SOLO el texto.",
            'etiquetas'         => "Genera 5-8 etiquetas separadas por coma. Devuelve SOLO las etiquetas en CSV.",
            'focus_keyword'     => "Devuelve la keyword principal de 2-4 palabras para este artículo. SOLO el texto.",
            'faq_json'          => "Genera 4-5 preguntas frecuentes sobre el tema. Devuelve SOLO JSON válido: [{\"question\":\"...\",\"answer\":\"...\"}]",
            'image_alt'         => "Genera un texto ALT descriptivo para la imagen principal (máx. 125 chars). SOLO el texto.",
            'ai_context_summary'=> "Genera un resumen interno de 80-100 palabras del artículo para uso de la IA. SOLO el texto.",
            'summary_short'     => "Genera un resumen de 20-25 palabras para el panel de administración. SOLO el texto.",
        ];

        $fieldInstruction = $fieldPrompts[$field] ?? "Regenera el campo '$field'. Devuelve SOLO el valor.";

        $contextStr = "TÍTULO: {$context['titulo']}\nKEYWORD: {$context['focus_keyword']}\nEXTRACTO: {$context['extracto']}\nCONTENIDO (primeras 500 chars): " . substr($context['contenido'] ?? '', 0, 500);

        $userPrompt = $contextStr . "\n\n" . $fieldInstruction;

        $response = $this->call($systemPrompt, $userPrompt, 1024);
        $text = trim($response['content'][0]['text'] ?? '');
        $this->lastUsage = [
            'input_tokens'  => $response['usage']['input_tokens'] ?? 0,
            'output_tokens' => $response['usage']['output_tokens'] ?? 0,
        ];

        // Para faq_json devolver directamente el JSON
        if ($field === 'faq_json') {
            $json = $this->extractJson($text);
            return $json ? json_encode($json, JSON_UNESCAPED_UNICODE) : $text;
        }

        return $text;
    }

    private function callWithTool(string $system, string $user): array
    {
        $tool = [
            'name'         => 'create_article',
            'description'  => 'Crea un artículo SEO completo con todos sus metadatos',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['titulo', 'slug', 'contenido', 'extracto', 'meta_title', 'meta_description'],
                'properties' => [
                    'titulo'                    => ['type' => 'string'],
                    'slug'                      => ['type' => 'string'],
                    'contenido'                 => ['type' => 'string', 'description' => 'Markdown completo del artículo'],
                    'extracto'                  => ['type' => 'string'],
                    'focus_keyword'             => ['type' => 'string'],
                    'etiquetas'                 => ['type' => 'string'],
                    'schema_type'               => ['type' => 'string'],
                    'meta_title'                => ['type' => 'string'],
                    'meta_description'          => ['type' => 'string'],
                    'image_prompt'              => ['type' => 'string'],
                    'image_alt'                 => ['type' => 'string'],
                    'ai_context_summary'        => ['type' => 'string'],
                    'summary_short'             => ['type' => 'string'],
                    'faq' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'question' => ['type' => 'string'],
                                'answer'   => ['type' => 'string'],
                            ],
                            'required' => ['question', 'answer'],
                        ],
                    ],
                    'internal_links_suggested' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'titulo' => ['type' => 'string'],
                                'slug'   => ['type' => 'string'],
                                'anchor' => ['type' => 'string'],
                                'razon'  => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model'       => $this->model,
            'max_tokens'  => 16000,
            'system'      => $system,
            'tools'       => [$tool],
            'tool_choice' => ['type' => 'tool', 'name' => 'create_article'],
            'messages'    => [['role' => 'user', 'content' => $user]],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Error Anthropic API: ' . $response->status() . ' — ' . $response->body());
        }

        return $response->json();
    }

    private function call(string $system, string $user, int $maxTokens): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'system'     => $system,
            'messages'   => [
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Error Anthropic API: ' . $response->status() . ' — ' . $response->body());
        }

        return $response->json();
    }

    private function extractJson(string $text): ?array
    {
        // Eliminar bloques de código markdown si los hay
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```$/m', '', $text);

        // Extraer el primer objeto o array JSON válido
        if (preg_match('/\{[\s\S]*\}/u', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    public function lastUsage(): array
    {
        return $this->lastUsage;
    }

    public function providerName(): string
    {
        return 'claude';
    }

    public function modelName(): string
    {
        return $this->model;
    }
}
