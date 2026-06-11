<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbiItem extends Model
{
    public const DIMENSION_EXHAUSTION = 'EX';
    public const DIMENSION_CYNICISM = 'CY';
    public const DIMENSION_PROFESSIONAL_EFFICACY = 'PE';

    protected $fillable = [
        'code',
        'dimension',
        'position',
        'prompt_text',
        'source_item_reference',
        'is_active',
        'licensed_content_loaded_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'prompt_text' => 'encrypted',
        'is_active' => 'boolean',
        'licensed_content_loaded_at' => 'datetime',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(MbiResponse::class, 'item_id');
    }
}
