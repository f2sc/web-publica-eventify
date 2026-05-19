<?php

namespace App\Services\AI\TextProviders;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiTextProvider implements AiTextProviderInterface
{
    private array $lastUsage = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini'
    ) {}

    public function generateArticle(string $userPrompt, string $systemPrompt): array
    {
        $response = Http::timeout(120)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $this->model,
                'max_tokens'      => 8192,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI error ' . $response->status() . ': ' . ($response->json('error.message') ?? $response->body()));
        }

        $this->lastUsage = [
            'input_tokens'  => $response->json('usage.prompt_tokens') ?? 0,
            'output_tokens' => $response->json('usage.completion_tokens') ?? 0,
        ];

        $text = $response->json('choices.0.message.content') ?? '';
        $json = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('OpenAI no devolvió JSON válido: ' . $text);
        }

        return $json;
    }

    public function regenerateField(string $field, array $context, string $systemPrompt): string
    {
        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => $this->model,
                'max_tokens' => 512,
                'messages'   => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => "Campo a regenerar: $field\nContexto: " . json_encode($context)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI error ' . $response->status() . ': ' . ($response->json('error.message') ?? $response->body()));
        }

        $this->lastUsage = [
            'input_tokens'  => $response->json('usage.prompt_tokens') ?? 0,
            'output_tokens' => $response->json('usage.completion_tokens') ?? 0,
        ];

        return trim($response->json('choices.0.message.content') ?? '');
    }

    public function lastUsage(): array
    {
        return $this->lastUsage;
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
