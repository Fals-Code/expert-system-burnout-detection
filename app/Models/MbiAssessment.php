<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbiAssessment extends Model
{
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_INSUFFICIENT_DATA = 'INSUFFICIENT_DATA';

    protected $fillable = [
        'user_id', 'instrument_code', 'instrument_version', 'status', 'responses_count',
        'ex_total', 'ex_score', 'cy_total', 'cy_score', 'pe_total', 'pe_score',
        'profile_code', 'profile_basis', 'has_red_flag', 'red_flag_response',
        'red_flag_codes', 'disclaimer_version', 'completed_at',
    ];

    protected $casts = [
        'responses_count' => 'integer',
        'ex_total' => 'float',
        'ex_score' => 'float',
        'cy_total' => 'float',
        'cy_score' => 'float',
        'pe_total' => 'float',
        'pe_score' => 'float',
        'has_red_flag' => 'boolean',
        'red_flag_response' => 'integer',
        'red_flag_codes' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(MbiResponse::class, 'assessment_id');
    }
}
