<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aturan extends Model
{
    use SoftDeletes;

    protected $table = 'aturan';

    protected $fillable = [
        'kode',
        'diagnosa_id',
        'cf_pakar',
        'prioritas',
        'is_active',
        'deskripsi',
        'min_threshold',
    ];

    protected $casts = [
        'diagnosa_id' => 'integer',
        'cf_pakar' => 'float',
        'prioritas' => 'integer',
        'is_active' => 'boolean',
        'min_threshold' => 'float',
    ];

    public function diagnosa(): BelongsTo
    {
        return $this->belongsTo(Diagnosa::class);
    }

    public function gejala(): BelongsToMany
    {
        return $this->belongsToMany(Gejala::class, 'aturan_gejala', 'aturan_id', 'gejala_id')
            ->withPivot(['bobot_pakar', 'evidence_direction']);
    }
}
