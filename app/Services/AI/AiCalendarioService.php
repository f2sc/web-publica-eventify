<?php
namespace App\Services\AI;

use Throwable;

class AiCalendarioService
{
    public function generateIdeas(string $descripcion, ?int $categoriaId = null): array
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        $prompt = "Dame exactamente 5 ideas de títulos SEO para artículos de blog sobre este tema:\n\n{$descripcion}\n\nDevuelve EXCLUSIVAMENTE este JSON (sin texto adicional):\n{\"items\":[\"Título 1\",\"Título 2\",\"Título 3\",\"Título 4\",\"Título 5\"]}";

        try {
            $result = $provider->generateArticle($prompt, $settings->prompt_system ?? '');
            $usage  = $provider->lastUsage();
            AiCostLogger::log('calendario_ideas', $provider->providerName(), $provider->modelName(), null,
                $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);
            return $result['items'] ?? [];
        } catch (Throwable $e) {
            AiCostLogger::log('calendario_ideas', $provider->providerName(), $provider->modelName(), null,
                status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }
    }

    public function generateSeriePlan(string $nombre, string $descripcion, int $n): array
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        $prompt = "Crea un plan de {$n} artículos de blog para la serie '{$nombre}'.\n\nDescripción / audiencia / objetivo: {$descripcion}\n\nDevuelve EXCLUSIVAMENTE este JSON:\n{\"plan\":[{\"orden\":1,\"titulo\":\"...\",\"descripcion\":\"...1-2 frases...\",\"enlaza_a\":[]}]}\n\nCada artículo indica en 'enlaza_a' los números de orden de artículos anteriores que debe enlazar.";

        try {
            $result = $provider->generateArticle($prompt, $settings->prompt_system ?? '');
            $usage  = $provider->lastUsage();
            AiCostLogger::log('calendario_plan', $provider->providerName(), $provider->modelName(), null,
                $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);
            return $result['plan'] ?? [];
        } catch (Throwable $e) {
            AiCostLogger::log('calendario_plan', $provider->providerName(), $provider->modelName(), null,
                status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }
    }
}
