<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbi_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('dimension', 2)->index();
            $table->unsignedTinyInteger('position')->unique();
            $table->text('prompt_text');
            $table->boolean('is_reverse')->default(false);
            $table->string('locale', 8)->default('id');
            $table->string('source_reference', 160);
            $table->text('adaptation_note')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbi_items');
    }
};
