<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'titulo', 'slug', 'extracto', 'contenido', 'imagen_principal',
        'categoria_blog', 'etiquetas', 'meta_title', 'meta_description',
        'canonical', 'indexable', 'og_image', 'schema_type', 'autor',
        'estado', 'fecha_publicacion',
    ];

    protected $casts = [
        'indexable'          => 'boolean',
        'fecha_publicacion'  => 'datetime',
    ];

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('estado', 'publicado')
                     ->where('fecha_publicacion', '<=', now());
    }

    public function etiquetasArray(): array
    {
        return $this->etiquetas ? array_map('trim', explode(',', $this->etiquetas)) : [];
    }
}
