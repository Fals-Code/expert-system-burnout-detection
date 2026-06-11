<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbi_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('instrument_code', 16)->default('CBI');
            $table->string('instrument_version', 32)->default('2005-ID-adapted');
            $table->string('status', 32)->default('IN_PROGRESS')->index();
            $table->unsignedTinyInteger('responses_count')->default(0);
            $table->decimal('personal_total', 7, 2)->nullable();
            $table->decimal('personal_score', 5, 2)->nullable();
            $table->decimal('work_total', 7, 2)->nullable();
            $table->decimal('work_score', 5, 2)->nullable();
            $table->decimal('client_total', 7, 2)->nullable();
            $table->decimal('client_score', 5, 2)->nullable();
            $table->string('disclaimer_version', 48);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbi_assessments');
    }
};
