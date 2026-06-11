<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InferenceAnswer extends Model
{
    protected $fillable = [
        'session_id',
        'cbi_item_id',
        'answer_key',
        'raw_score',
        'boolean_value',
    ];

    protected $casts = [
        'raw_score' => 'integer',
        'boolean_value' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(InferenceSession::class, 'session_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CbiItem::class, 'cbi_item_id');
    }
}
