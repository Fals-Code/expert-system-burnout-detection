<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbiResponse extends Model
{
    protected $fillable = ['assessment_id', 'item_id', 'score'];

    protected $casts = ['score' => 'integer'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(MbiAssessment::class, 'assessment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(MbiItem::class, 'item_id');
    }
}
