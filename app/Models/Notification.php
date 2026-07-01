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
    public const CATEGORY_INFORMATION = 'informasi';

    public const CATEGORY_WARNING = 'peringatan';

    public const CATEGORY_REMINDER = 'pengingat';

    public const CATEGORY_SUPPORT = 'dukungan';

    public const CATEGORIES = [
        self::CATEGORY_INFORMATION,
        self::CATEGORY_WARNING,
        self::CATEGORY_REMINDER,
        self::CATEGORY_SUPPORT,
    ];

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

    public static function normalizeCategory(?string $category): string
    {
        return in_array($category, self::CATEGORIES, true)
            ? $category
            : self::CATEGORY_INFORMATION;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
