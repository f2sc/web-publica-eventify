<?php

namespace App\Services\AI;

use App\Models\Articulo;

class AiInternalLinker
{
    public function findRelated(string $keyword, ?int $categoriaId, int $limit): array
    {
        $articulos = Articulo::publicados()
            ->whereNotNull('ai_context_summary')
            ->select('id', 'titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
            ->orderByDesc('fecha_publicacion')
            ->get();

        if ($articulos->isEmpty()) {
            return [];
        }

        $keywords = array_filter(explode(' ', mb_strtolower($keyword)));

        return $articulos
            ->map(function ($a) use ($keywords, $categoriaId) {
                $text  = mb_strtolower($a->titulo . ' ' . $a->focus_keyword . ' ' . $a->ai_context_summary);
                $score = array_sum(array_map(fn ($k) => substr_count($text, $k), $keywords));
                // Bonus por misma categoría
                if ($categoriaId && $a->categoria_blog_id === $categoriaId) {
                    $score += 3;
                }
                return ['articulo' => $a, 'score' => $score];
            })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('articulo')
            ->map(fn ($a) => [
                'titulo'             => $a->titulo,
                'slug'               => $a->slug,
                'focus_keyword'      => $a->focus_keyword,
                'ai_context_summary' => $a->ai_context_summary,
            ])
            ->values()
            ->toArray();
    }

    public function formatForPrompt(array $articles): string
    {
        if (empty($articles)) {
            return 'No hay artículos anteriores relevantes.';
        }

        return collect($articles)->map(function ($a, $i) {
            return ($i + 1) . ". \"{$a['titulo']}\" — /blog/{$a['slug']}\n   Resumen: {$a['ai_context_summary']}";
        })->implode("\n\n");
    }
}
