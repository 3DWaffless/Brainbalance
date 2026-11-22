<?php
// FILE: database/migrations/2026_11_22_000001_upgrade_questions_for_rich_types_and_bank.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (!Schema::hasColumn('questions', 'type')) {
                    $table->string('type')->default('multiple_choice')->after('quiz_id');
                }
                if (!Schema::hasColumn('questions', 'options')) {
                    $table->json('options')->nullable();
                }
                if (!Schema::hasColumn('questions', 'correct_answers')) {
                    $table->json('correct_answers')->nullable();
                }
                if (!Schema::hasColumn('questions', 'matching_pairs')) {
                    $table->json('matching_pairs')->nullable();
                }
                if (!Schema::hasColumn('questions', 'points')) {
                    $table->unsignedInteger('points')->default(1);
                }
                if (!Schema::hasColumn('questions', 'explanation')) {
                    $table->text('explanation')->nullable();
                }
                if (!Schema::hasColumn('questions', 'position')) {
                    $table->unsignedInteger('position')->default(0);
                }
            });
        }

        if (Schema::hasTable('quizzes') && !Schema::hasColumn('quizzes', 'is_published')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->boolean('is_published')->default(true);
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback for SQLite safety
    }
};