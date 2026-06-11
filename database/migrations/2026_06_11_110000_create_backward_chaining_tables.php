<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('goal', 100)->index();
            $table->string('operator', 16)->default('ALL');
            $table->unsignedTinyInteger('required_count')->default(1);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['goal', 'priority']);
        });

        Schema::create('premises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('rules')->cascadeOnDelete();
            $table->string('premise_type', 16)->index();
            $table->string('premise_key', 100)->index();
            $table->foreignId('cbi_item_id')
                ->nullable()
                ->constrained('cbi_items')
                ->restrictOnDelete();
            $table->boolean('expected_boolean')->default(true);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('label', 180)->nullable();
            $table->timestamps();

            $table->unique([
                'rule_id',
                'premise_type',
                'premise_key',
            ], 'premises_rule_type_key_unique');
        });

        Schema::create('inference_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('root_goal', 100);
            $table->string('current_goal', 100)->nullable()->index();
            $table->json('goal_queue');
            $table->unsignedTinyInteger('goal_index')->default(0);
            $table->string('status', 32)->default('IN_PROGRESS')->index();
            $table->string('conclusion', 100)->nullable()->index();
            $table->string('current_question_code', 24)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('inference_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('inference_sessions')
                ->cascadeOnDelete();
            $table->foreignId('cbi_item_id')
                ->constrained('cbi_items')
                ->restrictOnDelete();
            $table->string('answer_key', 24);
            $table->unsignedTinyInteger('raw_score');
            $table->boolean('boolean_value');
            $table->timestamps();

            $table->unique(['session_id', 'cbi_item_id']);
        });

        Schema::create('inference_traces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('inference_sessions')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('event', 40);
            $table->string('goal', 100)->nullable();
            $table->string('rule_code', 64)->nullable();
            $table->string('premise_key', 100)->nullable();
            $table->boolean('result')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'sequence']);
            $table->index(['session_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inference_traces');
        Schema::dropIfExists('inference_answers');
        Schema::dropIfExists('inference_sessions');
        Schema::dropIfExists('premises');
        Schema::dropIfExists('rules');
    }
};
