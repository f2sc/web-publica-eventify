<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suscriptor extends Model
{
    protected $table    = 'suscriptores';
    protected $fillable = ['nombre', 'email', 'fuente', 'token_confirmacion', 'confirmado', 'confirmed_at', 'unsubscribed_at'];

    protected $casts = [
        'confirmado'       => 'boolean',
        'confirmed_at'     => 'datetime',
        'unsubscribed_at'  => 'datetime',
    ];
}
