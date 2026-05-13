<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update tingkat diagnosa
        // Di MySQL, mengganti enum butuh raw query atau ganti kolom
        Schema::table('diagnosa', function (Blueprint $table) {
            $table->string('tingkat', 50)->change();
        });

        // 2. Tambah tracing ke konsultasi untuk Explanation Facility
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->json('tracing')->nullable()->after('cf_final');
        });

        // 3. Tambah bobot_pakar ke pivot aturan_gejala untuk akurasi per aturan
        Schema::table('aturan_gejala', function (Blueprint $table) {
            $table->float('bobot_pakar')->default(0)->after('gejala_id');
        });
    }

    public function down(): void
    {
        Schema::table('aturan_gejala', function (Blueprint $table) {
            $table->dropColumn('bobot_pakar');
        });

        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropColumn('tracing');
        });

        Schema::table('diagnosa', function (Blueprint $table) {
            $table->enum('tingkat', ['RINGAN', 'SEDANG', 'BERAT'])->change();
        });
    }
};
