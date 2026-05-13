<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Aturan extends Model
{
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
