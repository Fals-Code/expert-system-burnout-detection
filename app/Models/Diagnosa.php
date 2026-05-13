<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnosa extends Model
{
    protected $table = 'diagnosa';
    protected $fillable = ['kode', 'nama', 'tingkat', 'deskripsi', 'saran', 'color', 'bg_light'];

    public function aturan(): HasMany
    {
        return $this->hasMany(Aturan::class);
    }
}
