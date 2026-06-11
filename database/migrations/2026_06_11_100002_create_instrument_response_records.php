<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbi_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')
                ->constrained('cbi_assessments')
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained('cbi_items')
                ->restrictOnDelete();
            $table->string('answer_key', 24);
            $table->unsignedTinyInteger('raw_score');
            $table->unsignedTinyInteger('normalized_score');
            $table->timestamps();

            $table->unique(['assessment_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbi_responses');
    }
};
