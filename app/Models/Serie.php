<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Serie extends Model
{
    protected $table = 'series';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'categoria_blog_id'];

    public function categoriaBlog(): BelongsTo
    {
        return $this->belongsTo(CategoriaBlog::class, 'categoria_blog_id');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'serie_id')->orderBy('orden_en_serie');
    }
}
