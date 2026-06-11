<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InferenceSession extends Model
{
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_PROVEN = 'PROVEN';
    public const STATUS_EXHAUSTED = 'EXHAUSTED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'user_id',
        'root_goal',
        'current_goal',
        'goal_queue',
        'goal_index',
        'status',
        'conclusion',
        'current_question_code',
        'completed_at',
    ];

    protected $casts = [
        'goal_queue' => 'array',
        'goal_index' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InferenceAnswer::class, 'session_id');
    }

    public function traces(): HasMany
    {
        return $this->hasMany(InferenceTrace::class, 'session_id')
            ->orderBy('sequence');
    }
}
