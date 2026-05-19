<?php

namespace App\Services\AI\TextProviders;

interface AiTextProviderInterface
{
    public function generateArticle(string $userPrompt, string $systemPrompt): array;

    public function regenerateField(string $field, array $context, string $systemPrompt): string;

    public function lastUsage(): array;

    public function providerName(): string;

    public function modelName(): string;
}
