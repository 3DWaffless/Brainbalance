<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->decimal('mastery_level', 5, 2)->default(0);
            $table->string('status')->default('not_started'); // not started, in progress, mastered
            $table->integer('attempts_count')->default(0);
            $table->text('last_ai_explanation')->nullable();
            $table->dateTime('ai_triggered_at')->nullable();
            $table->dateTime('last_attempted_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
