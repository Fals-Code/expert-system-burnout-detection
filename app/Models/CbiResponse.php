<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbiResponse extends Model
{
    protected $fillable = [
        'assessment_id',
        'item_id',
        'answer_key',
        'raw_score',
        'normalized_score',
    ];

    protected $casts = [
        'raw_score' => 'integer',
        'normalized_score' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(CbiAssessment::class, 'assessment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CbiItem::class, 'item_id');
    }
}
