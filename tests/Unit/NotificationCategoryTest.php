<?php

namespace Tests\Unit;

use App\Models\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NotificationCategoryTest extends TestCase
{
    public static function validCategories(): array
    {
        return array_map(
            static fn (string $category): array => [$category],
            Notification::CATEGORIES
        );
    }

    #[DataProvider('validCategories')]
    public function test_valid_notification_categories_are_preserved(string $category): void
    {
        $this->assertSame($category, Notification::normalizeCategory($category));
    }

    public function test_unknown_notification_category_falls_back_to_information(): void
    {
        $this->assertSame(
            Notification::CATEGORY_INFORMATION,
            Notification::normalizeCategory('kategori-tidak-valid')
        );
    }
}
