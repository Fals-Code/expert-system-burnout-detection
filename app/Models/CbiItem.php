<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbiItem extends Model
{
    public const DIMENSION_PERSONAL = 'PB';
    public const DIMENSION_WORK = 'WB';
    public const DIMENSION_CLIENT = 'CB';

    protected $fillable = [
        'code',
        'dimension',
        'position',
        'prompt_text',
        'is_reverse',
        'locale',
        'source_reference',
        'adaptation_note',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_reverse' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(InferenceAnswer::class, 'cbi_item_id');
    }

    public function premises(): HasMany
    {
        return $this->hasMany(ExpertPremise::class, 'cbi_item_id');
    }
}
