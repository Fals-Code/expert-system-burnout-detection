<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InferenceTrace extends Model
{
    protected $fillable = [
        'session_id',
        'sequence',
        'event',
        'goal',
        'rule_code',
        'premise_key',
        'result',
        'message',
        'context',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'result' => 'boolean',
        'context' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(InferenceSession::class, 'session_id');
    }
}
