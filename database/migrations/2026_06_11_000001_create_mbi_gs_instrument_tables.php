<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('dimension', 2)->index();
            $table->unsignedTinyInteger('position');
            $table->longText('prompt_text')->nullable();
            $table->string('source_item_reference', 64)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('licensed_content_loaded_at')->nullable();
            $table->timestamps();
            $table->unique(['dimension', 'position']);
        });

        Schema::create('mbi_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('instrument_code', 16)->default('MBI-GS');
            $table->string('instrument_version', 24)->default('1996/2016');
            $table->string('status', 32)->default('IN_PROGRESS')->index();
            $table->unsignedTinyInteger('responses_count')->default(0);
            $table->decimal('ex_total', 5, 2)->nullable();
            $table->decimal('ex_score', 4, 2)->nullable();
            $table->decimal('cy_total', 5, 2)->nullable();
            $table->decimal('cy_score', 4, 2)->nullable();
            $table->decimal('pe_total', 5, 2)->nullable();
            $table->decimal('pe_score', 4, 2)->nullable();
            $table->string('profile_code', 48)->nullable()->index();
            $table->string('profile_basis', 120)->nullable();
            $table->boolean('has_red_flag')->default(false)->index();
            $table->unsignedTinyInteger('red_flag_response')->nullable();
            $table->json('red_flag_codes')->nullable();
            $table->string('disclaimer_version', 48);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('mbi_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('mbi_assessments')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('mbi_items')->restrictOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();
            $table->unique(['assessment_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_responses');
        Schema::dropIfExists('mbi_assessments');
        Schema::dropIfExists('mbi_items');
    }
};
