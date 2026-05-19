<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ai_settings';
    protected $guarded = [];

    protected $casts = [
        'auto_generate_image' => 'boolean',
        'auto_generate_faq'   => 'boolean',
        'always_draft'        => 'boolean',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function getTextApiKeyAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setTextApiKeyAttribute(?string $value): void
    {
        $this->attributes['text_api_key'] = $value ? encrypt($value) : null;
    }

    public function getImageApiKeyAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setImageApiKeyAttribute(?string $value): void
    {
        $this->attributes['image_api_key'] = $value ? encrypt($value) : null;
    }
}
