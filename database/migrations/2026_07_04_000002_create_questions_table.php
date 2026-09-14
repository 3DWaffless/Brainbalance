<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type')->default('multiple_choice'); // e.g. multiple choice, identification
            $table->string('difficulty_level')->nullable();
            $table->text('correct_answer')->nullable();
            $table->json('options')->nullable(); // answer choices for multiple choice
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
