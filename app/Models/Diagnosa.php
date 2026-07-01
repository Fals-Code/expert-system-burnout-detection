<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property string $tingkat
 * @property string|null $deskripsi
 * @property string|null $saran
 * @property string|null $color
 * @property string|null $bg_light
 */
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
