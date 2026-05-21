<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Services\AI\AiArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

            $slug = $this->uniqueSlug(
                $result['slug'] ?? Str::slug($result['titulo'] ?? 'borrador')
            );

            $articulo = Articulo::create([
                'titulo'               => $result['titulo']             ?? 'Borrador sin título',
                'slug'                 => $slug,
                'extracto'             => $result['extracto']           ?? null,
                'contenido'            => $result['contenido']          ?? null,
                'focus_keyword'        => $result['focus_keyword']      ?? null,
                'etiquetas'            => $result['etiquetas']          ?? null,
                'schema_type'          => $result['schema_type']        ?? 'BlogPosting',
                'meta_title'           => $result['meta_title']         ?? null,
                'meta_description'     => $result['meta_description']   ?? null,
                'image_alt'            => $result['image_alt']          ?? null,
                'ai_context_summary'   => $result['ai_context_summary'] ?? null,
                'summary_short'        => $result['summary_short']      ?? null,
                'faq_json'             => $result['faq_json']           ?? null,
                'imagen_principal'     => $result['imagen_principal']   ?? null,
                'estado'               => 'borrador',
                'categoria_blog_id'    => $input['categoria_id']        ?? null,
                'serie_id'             => $input['serie_id']            ?? null,
                'orden_en_serie'       => $input['orden_en_serie']      ?? null,
                'ai_generated'         => true,
                'ai_last_provider'     => $result['ai_last_provider']   ?? null,
                'ai_last_model'        => $result['ai_last_model']      ?? null,
                'ai_last_generated_at' => now(),
            ]);

            return response()->json([
                'ok'         => true,
                'data'       => $result,
                'article_id' => $articulo->id,
                'edit_url'   => route('admin.articulos.edit', $articulo),
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function uniqueSlug(string $slug): string
    {
        if (empty($slug)) {
            $slug = 'borrador-' . now()->format('YmdHis');
        }
        $base = $slug;
        $i    = 1;
        while (Articulo::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
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
        $data  = $request->validate(['prompt' => ['nullable', 'string', 'max:2000']]);
        $prompt = !empty($data['prompt']) ? $data['prompt'] : $articulo->titulo;

        try {
            $url = $this->service->generateImage($prompt, $articulo->id);
            $articulo->update(['imagen_principal' => $url]);
            return response()->json(['ok' => true, 'url' => $url]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
