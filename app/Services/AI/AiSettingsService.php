<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Services\AI\ImageProviders\AiImageProviderInterface;
use App\Services\AI\ImageProviders\GoogleImageProvider;
use App\Services\AI\ImageProviders\OpenAiImageProvider;
use App\Services\AI\TextProviders\AiTextProviderInterface;
use App\Services\AI\TextProviders\ClaudeTextProvider;
use App\Services\AI\TextProviders\OpenAiTextProvider;

class AiSettingsService
{
    public static function get(): AiSetting
    {
        return AiSetting::instance();
    }

    public static function textProvider(): AiTextProviderInterface
    {
        $s      = self::get();
        $apiKey = $s->text_api_key ?: match ($s->text_provider ?? 'claude') {
            'openai' => env('OPENAI_API_KEY', ''),
            default  => env('ANTHROPIC_API_KEY', ''),
        };

        return match ($s->text_provider) {
            'openai' => new OpenAiTextProvider($apiKey, $s->text_model ?: 'gpt-4o-mini'),
            default  => new ClaudeTextProvider($apiKey, $s->text_model ?: 'claude-sonnet-4-6'),
        };
    }

    public static function imageProvider(): AiImageProviderInterface
    {
        $s      = self::get();
        $apiKey = $s->image_api_key ?: match ($s->image_provider ?? 'google') {
            'openai' => env('OPENAI_API_KEY', ''),
            default  => env('GOOGLE_API_KEY', ''),
        };

        return match ($s->image_provider) {
            'openai' => new OpenAiImageProvider($apiKey, $s->image_model ?: 'dall-e-3'),
            default  => new GoogleImageProvider($apiKey, $s->image_model ?: 'imagen-4.0-flash-exp'),
        };
    }

    public static function defaultSystemPrompt(AiSetting $s): string
    {
        return <<<PROMPT
Eres un redactor SEO experto en marketing local para comercios, bares, restaurantes y asociaciones de vecinos en España.
Escribes siempre en español de España con tono {$s->default_tone}.
Eventify es la plataforma para la que escribes: ayuda a comercios locales a captar clientes vía QR y fidelizarlos con campañas.
No inventes datos concretos, cifras legales ni estadísticas si no se proporcionan.
Devuelve EXCLUSIVAMENTE JSON válido con la estructura indicada en el mensaje del usuario. Sin texto adicional fuera del JSON.
PROMPT;
    }
}
