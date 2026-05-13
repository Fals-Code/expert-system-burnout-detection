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
    protected $fillable = ['kode', 'diagnosa_id', 'cf_pakar'];

    public function diagnosa(): BelongsTo
    {
        return $this->belongsTo(Diagnosa::class);
    }

    public function gejala(): BelongsToMany
    {
        return $this->belongsToMany(Gejala::class, 'aturan_gejala', 'aturan_id', 'gejala_id');
    }
}
