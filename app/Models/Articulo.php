<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Articulo extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'titulo', 'slug', 'extracto', 'contenido', 'imagen_principal',
        'categoria_blog_id', 'categoria_blog', 'etiquetas', 'meta_title', 'meta_description',
        'canonical', 'indexable', 'og_image', 'schema_type', 'autor',
        'estado', 'fecha_publicacion',
    ];

    public function categoriaBlog(): BelongsTo
    {
        return $this->belongsTo(CategoriaBlog::class, 'categoria_blog_id');
    }

    protected $casts = [
        'indexable'          => 'boolean',
        'fecha_publicacion'  => 'datetime',
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
