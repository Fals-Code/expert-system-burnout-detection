<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbiAssessment extends Model
{
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_INSUFFICIENT_DATA = 'INSUFFICIENT_DATA';

    protected $fillable = [
        'user_id',
        'instrument_code',
        'instrument_version',
        'status',
        'responses_count',
        'personal_total',
        'personal_score',
        'work_total',
        'work_score',
        'client_total',
        'client_score',
        'disclaimer_version',
        'completed_at',
    ];

    protected $casts = [
        'responses_count' => 'integer',
        'personal_total' => 'float',
        'personal_score' => 'float',
        'work_total' => 'float',
        'work_score' => 'float',
        'client_total' => 'float',
        'client_score' => 'float',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CbiResponse::class, 'assessment_id');
    }
}
