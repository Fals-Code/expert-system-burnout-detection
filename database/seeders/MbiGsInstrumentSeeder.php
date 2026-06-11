<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MbiGsInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $items = [];
        $globalPosition = 1;

        foreach (['EX' => 5, 'CY' => 5, 'PE' => 6] as $dimension => $count) {
            for ($dimensionPosition = 1; $dimensionPosition <= $count; $dimensionPosition++) {
                $items[] = [
                    'code' => sprintf('MBIGS-%s-%02d', $dimension, $dimensionPosition),
                    'dimension' => $dimension,
                    'position' => $globalPosition++,
                    'prompt_text' => null,
                    'source_item_reference' => null,
                    'is_active' => true,
                    'licensed_content_loaded_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('mbi_items')->upsert(
            $items,
            ['code'],
            ['dimension', 'position', 'is_active', 'updated_at']
        );

        DB::table('mbi_items')
            ->whereNotIn('code', collect($items)->pluck('code')->all())
            ->update(['is_active' => false, 'updated_at' => $now]);

        if (Schema::hasTable('aturan') && Schema::hasColumn('aturan', 'is_active')) {
            DB::table('aturan')->update(['is_active' => false, 'updated_at' => $now]);
        }
    }
}
