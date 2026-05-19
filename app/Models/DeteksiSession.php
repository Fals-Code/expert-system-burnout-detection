<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeteksiSession extends Model
{
    protected $table = 'deteksi_sessions';
    protected $fillable = ['user_id', 'answers', 'current_step_codes'];

    protected $casts = [
        'answers' => 'array',
        'current_step_codes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
