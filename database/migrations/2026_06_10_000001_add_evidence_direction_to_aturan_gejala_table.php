<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('aturan_gejala', 'evidence_direction')) {
            Schema::table('aturan_gejala', function (Blueprint $table) {
                $table->enum('evidence_direction', [
                    'PRESENT_SUPPORTS',
                    'ABSENT_SUPPORTS',
                ])->default('PRESENT_SUPPORTS')->after('gejala_id');
            });
        }

        /**
         * Default rule direction:
         * - PRESENT_SUPPORTS means the symptom being present supports the diagnosis.
         * - ABSENT_SUPPORTS means the symptom being absent supports the diagnosis.
         *
         * Since diagnosa_id = 1 is now "Tidak Burnout (Kondisi Sehat)",
         * stress/burnout symptoms attached to its rules should support the diagnosis
         * only when the user does NOT experience those symptoms.
         */
        DB::table('aturan_gejala')
            ->whereIn('aturan_id', function ($query) {
                $query->select('id')
                    ->from('aturan')
                    ->where('diagnosa_id', 1);
            })
            ->update([
                'evidence_direction' => 'ABSENT_SUPPORTS',
            ]);

        DB::table('aturan_gejala')
            ->whereIn('aturan_id', function ($query) {
                $query->select('id')
                    ->from('aturan')
                    ->where('diagnosa_id', '<>', 1);
            })
            ->update([
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ]);

        $this->flushExpertSystemCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('aturan_gejala', 'evidence_direction')) {
            Schema::table('aturan_gejala', function (Blueprint $table) {
                $table->dropColumn('evidence_direction');
            });
        }

        $this->flushExpertSystemCache();
    }

    /**
     * Clear knowledge-base cache so updated rules are read immediately.
     */
    private function flushExpertSystemCache(): void
    {
        Cache::forget('aturan_active_rules_base64');
        Cache::forget('diagnosa_ordered_base64');
        Cache::forget('diagnosa_default_rendah_base64');
        Cache::forget('diagnosa_default_tidak_burnout_base64');

        DB::table('diagnosa')
            ->pluck('id')
            ->each(function ($id) {
                Cache::forget("aturan_by_diagnosa_{$id}_base64");
            });
    }
};
