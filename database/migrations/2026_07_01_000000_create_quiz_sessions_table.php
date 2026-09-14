<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('subject', ['math', 'english']);
            $table->unsignedTinyInteger('total_questions')->default(5);
            $table->unsignedTinyInteger('score')->default(0);
            $table->enum('final_level', ['easy', 'medium', 'hard'])->default('easy');
            $table->json('question_log')->nullable(); // per-question history: question, answer, correct, level
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};