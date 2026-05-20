<?php

namespace App\Services\AI;

use App\Models\Articulo;

class AiInternalLinker
{
    public function findRelated(string $keyword, ?int $categoriaId, int $limit, array $forcedIds = []): array
    {
        // --- Forced articles (previous serie articles) ---
        $forced = [];
        if (!empty($forcedIds)) {
            $keyedByID = Articulo::publicados()
                ->whereIn('id', $forcedIds)
                ->select('id', 'titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
                ->get()
                ->keyBy('id');

            // Maintain the order given in $forcedIds
            foreach ($forcedIds as $fid) {
                if (isset($keyedByID[$fid])) {
                    $a = $keyedByID[$fid];
                    $forced[] = [
                        'titulo'             => $a->titulo,
                        'slug'               => $a->slug,
                        'focus_keyword'      => $a->focus_keyword,
                        'ai_context_summary' => $a->ai_context_summary,
                    ];
                }
            }
        }

        $remaining = max(0, $limit - count($forced));
        if ($remaining === 0) {
            return $forced;
        }

        // --- Keyword-scored articles (excluding forced IDs) ---
        $articulos = Articulo::publicados()
            ->whereNotIn('id', $forcedIds)
            ->whereNotNull('ai_context_summary')
            ->select('id', 'titulo', 'slug', 'focus_keyword', 'ai_context_summary', 'categoria_blog_id')
            ->orderByDesc('fecha_publicacion')
            ->get();

        if ($articulos->isEmpty()) {
            return $forced;
        }

        $keywords = array_filter(explode(' ', mb_strtolower($keyword)));

        $byKeyword = $articulos
            ->map(function ($a) use ($keywords, $categoriaId) {
                $text  = mb_strtolower($a->titulo . ' ' . $a->focus_keyword . ' ' . $a->ai_context_summary);
                $score = array_sum(array_map(fn ($k) => substr_count($text, $k), $keywords));
                if ($categoriaId && $a->categoria_blog_id === $categoriaId) {
                    $score += 3;
                }
                return ['articulo' => $a, 'score' => $score];
            })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($remaining)
            ->pluck('articulo')
            ->map(fn ($a) => [
                'titulo'             => $a->titulo,
                'slug'               => $a->slug,
                'focus_keyword'      => $a->focus_keyword,
                'ai_context_summary' => $a->ai_context_summary,
            ])
            ->values()
            ->toArray();

        return array_merge($forced, $byKeyword);
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
