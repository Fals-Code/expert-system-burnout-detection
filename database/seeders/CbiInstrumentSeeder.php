<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CbiInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $source = 'Kristensen et al. (2005), Copenhagen Burnout Inventory';
        $groups = [
            'PB' => require __DIR__.'/data/cbi_personal.php',
            'WB' => require __DIR__.'/data/cbi_work.php',
            'CB' => require __DIR__.'/data/cbi_client.php',
        ];

        $items = [];
        $position = 1;

        foreach ($groups as $dimension => $definitions) {
            foreach ($definitions as $definition) {
                [$code, $prompt] = $definition;
                $isReverse = (bool) ($definition[2] ?? false);

                $items[] = [
                    'code' => $code,
                    'dimension' => $dimension,
                    'position' => $position++,
                    'prompt_text' => $prompt,
                    'is_reverse' => $isReverse,
                    'locale' => 'id',
                    'source_reference' => $source,
                    'adaptation_note' => $this->note($code),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('cbi_items')->upsert($items, ['code'], [
            'dimension',
            'position',
            'prompt_text',
            'is_reverse',
            'locale',
            'source_reference',
            'adaptation_note',
            'is_active',
            'updated_at',
        ]);

        DB::table('cbi_items')
            ->whereNotIn('code', collect($items)->pluck('code')->all())
            ->update(['is_active' => false, 'updated_at' => $now]);

        if (Schema::hasTable('aturan') && Schema::hasColumn('aturan', 'is_active')) {
            DB::table('aturan')->update(['is_active' => false, 'updated_at' => $now]);
        }
    }

    private function note(string $code): ?string
    {
        return match ($code) {
            'CBI-WB-07' => 'Item positif; skor dibalik pada saat perhitungan.',
            'CBI-CB-01' => 'Penerima layanan mencakup pelanggan, pasien, siswa, pengguna, warga, atau pihak internal yang menerima hasil kerja.',
            default => null,
        };
    }
}
