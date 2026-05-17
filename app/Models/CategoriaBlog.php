<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaBlog extends Model
{
    protected $table    = 'categorias_blog';
    protected $fillable = ['nombre', 'slug', 'descripcion', 'meta_title', 'meta_description', 'og_image'];

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'categoria_blog_id');
    }

    public function articulosPublicados(): HasMany
    {
        return $this->hasMany(Articulo::class, 'categoria_blog_id')
                    ->publicados()
                    ->orderByDesc('fecha_publicacion');
    }
}
