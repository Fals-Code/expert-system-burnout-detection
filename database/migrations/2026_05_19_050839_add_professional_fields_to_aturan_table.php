<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aturan', function (Blueprint $table) {
            $table->integer('prioritas')->default(1)->after('cf_pakar');
            $table->boolean('is_active')->default(true)->after('prioritas');
            $table->text('deskripsi')->nullable()->after('is_active');
            $table->float('min_threshold')->default(0.20)->after('deskripsi'); // Ambang batas aktivasi aturan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aturan', function (Blueprint $table) {
            $table->dropColumn(['prioritas', 'is_active', 'deskripsi', 'min_threshold']);
        });
    }
};
