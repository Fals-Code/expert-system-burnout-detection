<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertPremise extends Model
{
    public const TYPE_FACT = 'FACT';
    public const TYPE_GOAL = 'GOAL';

    protected $table = 'premises';

    protected $fillable = [
        'rule_id',
        'premise_type',
        'premise_key',
        'cbi_item_id',
        'expected_boolean',
        'sequence',
        'label',
    ];

    protected $casts = [
        'expected_boolean' => 'boolean',
        'sequence' => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ExpertRule::class, 'rule_id');
    }

    public function cbiItem(): BelongsTo
    {
        return $this->belongsTo(CbiItem::class, 'cbi_item_id');
    }
}
