<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Services\AI\AiArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AiGenerateController extends Controller
{
    public function __construct(private readonly AiArticleService $service) {}

    // Generar artículo nuevo (sin ID aún — devuelve JSON para poblar el form)
    public function generate(Request $request): JsonResponse
    {
        $input = $request->validate([
            'idea'            => ['required', 'string', 'max:2000'],
            'focus_keyword'   => ['nullable', 'string', 'max:150'],
            'localidad'       => ['nullable', 'string', 'max:100'],
            'tono'            => ['nullable', 'string', 'max:100'],
            'instrucciones'   => ['nullable', 'string', 'max:500'],
            'categoria_id'    => ['nullable', 'integer'],
            'generate_image'  => ['boolean'],
            'generate_faq'    => ['boolean'],
            'suggest_links'   => ['boolean'],
            'serie_id'        => ['nullable', 'integer', 'exists:series,id'],
            'orden_en_serie'  => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->service->generate($input);
            return response()->json(['ok' => true, 'data' => $result]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Regenerar un campo individual en artículo existente
    public function regenerateField(Articulo $articulo, Request $request): JsonResponse
    {
        $data = $request->validate([
            'field'   => ['required', 'string'],
            'context' => ['nullable', 'array'],
        ]);

        $allowedFields = [
            'titulo', 'extracto', 'meta_title', 'meta_description',
            'etiquetas', 'focus_keyword', 'faq_json', 'image_alt',
            'ai_context_summary', 'summary_short',
        ];

        if (! in_array($data['field'], $allowedFields)) {
            return response()->json(['ok' => false, 'message' => 'Campo no permitido'], 422);
        }

        try {
            $value = $this->service->regenerateField($articulo, $data['field'], $data['context'] ?? []);
            return response()->json(['ok' => true, 'field' => $data['field'], 'value' => $value]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Generar solo imagen (artículo existente)
    public function generateImage(Articulo $articulo, Request $request): JsonResponse
    {
        $data  = $request->validate(['prompt' => ['nullable', 'string', 'max:1000']]);
        $prompt = $data['prompt'] ?? $articulo->titulo;

        try {
            $url = $this->service->generateImage($prompt, $articulo->id);
            $articulo->update(['imagen_principal' => $url]);
            return response()->json(['ok' => true, 'url' => $url]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
