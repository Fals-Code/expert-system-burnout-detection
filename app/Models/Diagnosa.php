<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosa extends Model
{
    use SoftDeletes;

    protected $table = 'diagnosa';
    protected $fillable = ['kode', 'nama', 'tingkat', 'deskripsi', 'saran', 'color', 'bg_light'];

    public function aturan(): HasMany
    {
        return $this->hasMany(Aturan::class);
    }

    public function konsultasi(): HasMany
    {
        return $this->hasMany(Konsultasi::class);
    }
}
