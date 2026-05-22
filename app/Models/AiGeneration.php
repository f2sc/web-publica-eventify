<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    public $timestamps = false;
    protected $table   = 'ai_generations';
    protected $guarded = [];
    protected $casts   = ['created_at' => 'datetime'];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'article_id');
    }
}
