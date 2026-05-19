<?php

namespace App\Services\AI;

use App\Models\AiGeneration;

class AiCostLogger
{
    private static array $costPerMillion = [
        'claude' => ['input' => 3.0,  'output' => 15.0],
        'openai' => ['input' => 0.15, 'output' => 0.60],
        'google' => ['input' => 0.0,  'output' => 0.0],
    ];

    public static function log(
        string $type,
        string $provider,
        string $model,
        ?int $articleId,
        int $inputTokens = 0,
        int $outputTokens = 0,
        ?string $fieldName = null,
        string $status = 'ok',
        ?string $errorMessage = null,
        float $fixedCost = 0.0
    ): AiGeneration {
        $rates = self::$costPerMillion[$provider] ?? ['input' => 0.0, 'output' => 0.0];

        $costUsd = $fixedCost > 0
            ? $fixedCost
            : (($inputTokens * $rates['input'] + $outputTokens * $rates['output']) / 1_000_000);

        return AiGeneration::create([
            'article_id'    => $articleId,
            'provider'      => $provider,
            'model'         => $model,
            'type'          => $type,
            'field_name'    => $fieldName,
            'input_tokens'  => $inputTokens ?: null,
            'output_tokens' => $outputTokens ?: null,
            'cost_usd'      => round($costUsd, 6),
            'status'        => $status,
            'error_message' => $errorMessage,
        ]);
    }
}
