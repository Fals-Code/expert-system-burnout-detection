<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property float $bobot
 * @property string $kategori
 */
class Gejala extends Model
{
    use SoftDeletes;

    protected $table = 'gejala';
    protected $fillable = ['kode', 'nama', 'bobot', 'kategori'];

    public function aturan(): BelongsToMany
    {
        return $this->belongsToMany(Aturan::class, 'aturan_gejala', 'gejala_id', 'aturan_id');
    }

    public function konsultasi(): BelongsToMany
    {
        return $this->belongsToMany(Konsultasi::class, 'konsultasi_gejala', 'gejala_id', 'konsultasi_id');
    }
}
