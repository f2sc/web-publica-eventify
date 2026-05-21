<?php

namespace App\Services\AI;

use App\Models\Articulo;
use Illuminate\Support\Str;
use Throwable;

class AiArticleService
{
    public function __construct(
        private readonly AiInternalLinker $linker
    ) {}

    public function generate(array $input, ?int $articleId = null): array
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        // 1. Contexto de artículos anteriores (con forzado de artículos de la serie)
        $forcedIds = [];
        if (!empty($input['serie_id']) && !empty($input['orden_en_serie'])) {
            $forcedIds = Articulo::where('serie_id', $input['serie_id'])
                ->where('orden_en_serie', '<', (int) $input['orden_en_serie'])
                ->publicados()
                ->pluck('id')
                ->toArray();
        }

        $related = $this->linker->findRelated(
            $input['focus_keyword'] ?? $input['idea'] ?? '',
            $input['categoria_id'] ?? null,
            $settings->max_articles_context,
            $forcedIds
        );
        $relatedContext = $this->linker->formatForPrompt($related);

        // 2. Construir prompts
        $systemPrompt = $settings->prompt_system ?: AiSettingsService::defaultSystemPrompt($settings);
        $userPrompt   = $this->buildUserPrompt($input, $relatedContext, $settings);

        // 3. Llamar IA de texto
        try {
            $result = $provider->generateArticle($userPrompt, $systemPrompt);
        } catch (Throwable $e) {
            AiCostLogger::log('full_article', $provider->providerName(), $provider->modelName(), $articleId, status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }

        $usage = $provider->lastUsage();
        AiCostLogger::log('full_article', $provider->providerName(), $provider->modelName(), $articleId, $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);

        // Generar slug si no viene
        if (empty($result['slug']) && !empty($result['titulo'])) {
            $result['slug'] = Str::slug($result['titulo']);
        }

        // Mapear faq como JSON string para el form (se guardará como TEXT en BD)
        if (isset($result['faq']) && is_array($result['faq'])) {
            $result['faq_json'] = $result['faq'];
        }
        unset($result['faq']);

        // 4. Imagen (si se pidió)
        if ($input['generate_image'] ?? false) {
            try {
                $imageUrl            = $this->generateImage($result['image_prompt'] ?? $result['titulo'], $articleId);
                $result['imagen_principal'] = $imageUrl;
            } catch (Throwable $e) {
                // La imagen falla de forma silenciosa — el artículo igual se genera
                $result['image_error'] = $e->getMessage();
            }
        }

        // Siempre borrador si está configurado
        if ($settings->always_draft) {
            $result['estado'] = 'borrador';
        }

        $result['ai_generated']    = true;
        $result['ai_last_provider'] = $provider->providerName();
        $result['ai_last_model']    = $provider->modelName();

        return $result;
    }

    public function regenerateField(Articulo $articulo, string $field, array $extraContext = []): string
    {
        $settings = AiSettingsService::get();
        $provider = AiSettingsService::textProvider();

        $context = array_merge([
            'titulo'        => $articulo->titulo,
            'extracto'      => $articulo->extracto,
            'contenido'     => $articulo->contenido,
            'focus_keyword' => $articulo->focus_keyword,
        ], $extraContext);

        $systemPrompt = $settings->prompt_system ?: AiSettingsService::defaultSystemPrompt($settings);

        try {
            $value = $provider->regenerateField($field, $context, $systemPrompt);
        } catch (Throwable $e) {
            AiCostLogger::log('field_regen', $provider->providerName(), $provider->modelName(), $articulo->id, fieldName: $field, status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }

        $usage = $provider->lastUsage();
        AiCostLogger::log('field_regen', $provider->providerName(), $provider->modelName(), $articulo->id, $usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0, $field);

        // Actualizar el artículo con el nuevo valor
        $articulo->update([$field => $value]);

        return $value;
    }

    public function generateImage(string $prompt, ?int $articleId = null): string
    {
        $settings      = AiSettingsService::get();
        $imageProvider = AiSettingsService::imageProvider();

        // Añadir estilo fotográfico global al prompt del usuario/IA
        $finalPrompt = trim($prompt, '. ');
        if (!empty($settings->prompt_image)) {
            $finalPrompt .= '. ' . $settings->prompt_image;
        }

        try {
            $url = $imageProvider->generate($finalPrompt, $settings->image_size ?? '1024x1024');
        } catch (Throwable $e) {
            AiCostLogger::log('image', $imageProvider->providerName(), $imageProvider->modelName(), $articleId, status: 'error', errorMessage: $e->getMessage());
            throw $e;
        }

        AiCostLogger::log('image', $imageProvider->providerName(), $imageProvider->modelName(), $articleId, fixedCost: $imageProvider->lastCost());

        return $url;
    }

    private function buildUserPrompt(array $input, string $relatedContext, $settings): string
    {
        $idea        = $input['idea']          ?? '';
        $keyword     = $input['focus_keyword'] ?? '';
        $tone        = $input['tono']          ?? $settings->default_tone;
        $localidad   = $input['localidad']     ?? '';
        $extra       = $input['instrucciones'] ?? '';
        $length      = $settings->default_length;
        $maxLinks    = $settings->max_internal_links;
        $generateFaq = ($input['generate_faq'] ?? true) ? 'Sí, incluye 4-5 preguntas frecuentes.' : 'No incluyas FAQ.';
        $suggestLinks= ($input['suggest_links'] ?? true) ? "Sugiere máximo {$maxLinks} enlaces internos sutiles si encajan." : 'No sugieras enlaces internos.';

        $localidadLine = $localidad ? "LOCALIDAD/ZONA: $localidad" : '';
        $extraLine     = $extra     ? "INSTRUCCIONES ADICIONALES: $extra" : '';

        return <<<PROMPT
Escribe un artículo SEO completo basado en esta idea:

IDEA PRINCIPAL: {$idea}
KEYWORD PRINCIPAL: {$keyword}
TONO: {$tone}
LONGITUD: {$length}
{$localidadLine}
{$extraLine}
FAQ: {$generateFaq}
ENLACES INTERNOS: {$suggestLinks}

ARTÍCULOS EXISTENTES (menciona 1-2 de forma natural si encaja):
{$relatedContext}

Devuelve EXCLUSIVAMENTE este JSON (sin texto adicional):
{
  "titulo": "...",
  "slug": "...",
  "contenido": "...Markdown completo con ## subtítulos...",
  "extracto": "...1-2 frases, máx 160 chars...",
  "focus_keyword": "...",
  "etiquetas": "tag1, tag2, tag3",
  "schema_type": "BlogPosting",
  "meta_title": "...máx 60 chars...",
  "meta_description": "...120-160 chars...",
  "faq": [{"question": "...", "answer": "..."}],
  "image_prompt": "...descripción fotorrealista en inglés para generar imagen...",
  "image_alt": "...",
  "ai_context_summary": "...80-100 palabras describiendo el artículo para uso interno de la IA...",
  "summary_short": "...20-25 palabras para el panel admin...",
  "internal_links_suggested": [{"titulo": "...", "slug": "...", "anchor": "...", "razon": "..."}]
}
PROMPT;
    }
}
