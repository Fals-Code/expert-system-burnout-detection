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
        // 2. Gejala
        Schema::create('gejala', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->float('bobot');
            $table->timestamps();
        });

        // 3. Diagnosa
        Schema::create('diagnosa', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->enum('tingkat', ['RINGAN', 'SEDANG', 'BERAT']);
            $table->text('deskripsi')->nullable();
            $table->text('saran')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('bg_light', 20)->nullable();
            $table->timestamps();
        });

        // 4. Aturan
        Schema::create('aturan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->foreignId('diagnosa_id')->constrained('diagnosa')->onDelete('cascade');
            $table->float('cf_pakar');
            $table->timestamps();
        });

        // 5. Aturan Gejala (Pivot)
        Schema::create('aturan_gejala', function (Blueprint $table) {
            $table->foreignId('aturan_id')->constrained('aturan')->onDelete('cascade');
            $table->foreignId('gejala_id')->constrained('gejala')->onDelete('cascade');
            $table->primary(['aturan_id', 'gejala_id']);
        });

        // 6. Konsultasi
        Schema::create('konsultasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('diagnosa_id')->constrained('diagnosa')->onDelete('cascade');
            $table->float('cf_final');
            $table->timestamps();
        });

        // 7. Konsultasi Gejala (Pivot)
        Schema::create('konsultasi_gejala', function (Blueprint $table) {
            $table->foreignId('konsultasi_id')->constrained('konsultasi')->onDelete('cascade');
            $table->foreignId('gejala_id')->constrained('gejala')->onDelete('cascade');
            $table->primary(['konsultasi_id', 'gejala_id']);
        });

        // 8. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('category', 32)->default('informasi');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // 9. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('entity');
            $table->text('desc');
            $table->timestamps();
        });

        // 10. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->string('kunci')->primary();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('konsultasi_gejala');
        Schema::dropIfExists('konsultasi');
        Schema::dropIfExists('aturan_gejala');
        Schema::dropIfExists('aturan');
        Schema::dropIfExists('diagnosa');
        Schema::dropIfExists('gejala');
        Schema::dropIfExists('divisi');
    }
};
