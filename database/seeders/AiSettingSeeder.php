<?php

namespace Database\Seeders;

use App\Models\AiSetting;
use Illuminate\Database\Seeder;

class AiSettingSeeder extends Seeder
{
    public function run(): void
    {
        AiSetting::updateOrCreate(['id' => 1], [
            'text_provider'        => 'claude',
            'text_model'           => 'claude-sonnet-4-6',
            'text_api_key'         => null,
            'image_provider'       => 'google',
            'image_model'          => 'imagen-4.0-flash-exp',
            'image_api_key'        => null,
            'image_size'           => '1024x1024',
            'image_style'          => null,
            'default_tone'         => 'profesional y cercano',
            'default_language'     => 'es',
            'default_length'       => '1000-1500 palabras',
            'max_articles_context' => 5,
            'max_internal_links'   => 2,
            'auto_generate_image'  => false,
            'auto_generate_faq'    => true,
            'always_draft'         => true,
            'prompt_system'        => <<<'PROMPT'
Eres un redactor SEO experto en marketing local para comercios, bares, restaurantes y asociaciones de vecinos en España.
Escribes siempre en español de España.
Eventify es la plataforma para la que escribes: ayuda a comercios locales a captar clientes vía QR y fidelizarlos con campañas.
No inventes datos concretos, cifras legales ni estadísticas si no se proporcionan.
Cuando menciones Eventify hazlo de forma natural, no forzada.
Devuelve EXCLUSIVAMENTE JSON válido con la estructura indicada en el mensaje del usuario. Sin texto adicional fuera del JSON.
PROMPT,
            'prompt_image'         => <<<'PROMPT'
Fotografía profesional, realista, con buena iluminación natural. Escena relacionada con el comercio local en España. Sin texto superpuesto. Estilo editorial moderno.
PROMPT,
            'prompt_interlinking'  => <<<'PROMPT'
Si alguno de los artículos anteriores es relevante para el tema actual, menciona 1-2 de forma natural en el contenido. No fuerces los enlaces. El anchor text debe ser descriptivo y fluir con el texto. No menciones más de 2 artículos anteriores.
PROMPT,
        ]);
    }
}
