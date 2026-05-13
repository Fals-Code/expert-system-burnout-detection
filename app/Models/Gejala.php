<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gejala extends Model
{
    protected $table = 'gejala';
    protected $fillable = ['kode', 'nama', 'bobot'];

    public function aturan(): BelongsToMany
    {
        return $this->belongsToMany(Aturan::class, 'aturan_gejala', 'gejala_id', 'aturan_id');
    }
}
