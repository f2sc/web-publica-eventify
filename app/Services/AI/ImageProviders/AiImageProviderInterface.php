<?php

namespace App\Services\AI\ImageProviders;

interface AiImageProviderInterface
{
    public function generate(string $prompt, string $size): string;

    public function lastCost(): float;

    public function providerName(): string;

    public function modelName(): string;
}
