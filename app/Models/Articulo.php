<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Articulo extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'titulo', 'slug', 'extracto', 'contenido', 'imagen_principal', 'image_alt',
        'categoria_blog_id', 'categoria_blog', 'etiquetas', 'focus_keyword',
        'meta_title', 'meta_description', 'canonical', 'indexable', 'og_image',
        'schema_type', 'faq_json', 'autor', 'estado', 'fecha_publicacion',
        'ai_context_summary', 'summary_short', 'ai_generated',
        'ai_last_provider', 'ai_last_model', 'ai_last_generated_at',
        'serie_id', 'orden_en_serie', 'enviar_newsletter',
    ];

    public function categoriaBlog(): BelongsTo
    {
        return $this->belongsTo(CategoriaBlog::class, 'categoria_blog_id');
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class, 'serie_id');
    }

    protected $casts = [
        'indexable'              => 'boolean',
        'fecha_publicacion'      => 'datetime',
        'faq_json'               => 'array',
        'ai_generated'           => 'boolean',
        'ai_last_generated_at'   => 'datetime',
        'enviar_newsletter'      => 'boolean',
    ];

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('estado', 'publicado')
                     ->where(function ($q) {
                         $q->whereNull('fecha_publicacion')
                           ->orWhere('fecha_publicacion', '<=', now());
                     });
    }

    public function etiquetasArray(): array
    {
        return $this->etiquetas ? array_map('trim', explode(',', $this->etiquetas)) : [];
    }

    public function tiempoLectura(): int
    {
        $palabras = str_word_count(strip_tags($this->contenido ?? ''));
        return max(1, (int) ceil($palabras / 200));
    }
}
