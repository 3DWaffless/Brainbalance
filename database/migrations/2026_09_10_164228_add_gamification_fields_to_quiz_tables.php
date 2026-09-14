<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_attempts', 'current_streak')) {
                $table->integer('current_streak')->default(0);
            }
            if (!Schema::hasColumn('quiz_attempts', 'max_streak')) {
                $table->integer('max_streak')->default(0);
            }
            if (!Schema::hasColumn('quiz_attempts', 'total_xp')) {
                $table->integer('total_xp')->default(0);
            }
        });

        Schema::table('question_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('question_responses', 'time_taken_seconds')) {
                $table->integer('time_taken_seconds')->nullable();
            }
            if (!Schema::hasColumn('question_responses', 'xp_earned')) {
                $table->integer('xp_earned')->default(0);
            }
            if (!Schema::hasColumn('question_responses', 'multiplier_applied')) {
                $table->float('multiplier_applied')->default(1.0);
            }
        });
    }

    public function down(): void {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('quiz_attempts', 'current_streak') ? 'current_streak' : null,
                Schema::hasColumn('quiz_attempts', 'max_streak') ? 'max_streak' : null,
                Schema::hasColumn('quiz_attempts', 'total_xp') ? 'total_xp' : null,
            ]));
        });

        Schema::table('question_responses', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('question_responses', 'time_taken_seconds') ? 'time_taken_seconds' : null,
                Schema::hasColumn('question_responses', 'xp_earned') ? 'xp_earned' : null,
                Schema::hasColumn('question_responses', 'multiplier_applied') ? 'multiplier_applied' : null,
            ]));
        });
    }
};