<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpertRule extends Model
{
    public const OPERATOR_ALL = 'ALL';
    public const OPERATOR_ANY = 'ANY';
    public const OPERATOR_K_OF_N = 'K_OF_N';

    protected $table = 'rules';

    protected $fillable = [
        'code',
        'goal',
        'operator',
        'required_count',
        'priority',
        'description',
        'is_active',
    ];

    protected $casts = [
        'required_count' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function premises(): HasMany
    {
        return $this->hasMany(ExpertPremise::class, 'rule_id')
            ->orderBy('sequence');
    }

    public function threshold(): int
    {
        $premiseCount = $this->premises->count();

        return match ($this->operator) {
            self::OPERATOR_ANY => 1,
            self::OPERATOR_K_OF_N => $this->required_count,
            default => $premiseCount,
        };
    }
}
