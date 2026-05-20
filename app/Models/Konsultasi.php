<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $diagnosa_id
 * @property float $cf_final
 * @property array $tracing
 */
class Konsultasi extends Model
{
    protected $table = 'konsultasi';
    protected $fillable = ['user_id', 'diagnosa_id', 'cf_final', 'tracing'];

    protected $casts = [
        'tracing' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diagnosa(): BelongsTo
    {
        return $this->belongsTo(Diagnosa::class)->withTrashed();
    }

    public function gejala(): BelongsToMany
    {
        return $this->belongsToMany(Gejala::class, 'konsultasi_gejala', 'konsultasi_id', 'gejala_id')->withTrashed();
    }
}
