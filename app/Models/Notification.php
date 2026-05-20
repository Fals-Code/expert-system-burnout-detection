<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $category
 * @property string $title
 * @property string $message
 * @property string $type
 * @property bool $is_read
 * @property string|null $icon
 * @property string|null $color
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'message',
        'type',
        'is_read',
        'icon',
        'color',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
