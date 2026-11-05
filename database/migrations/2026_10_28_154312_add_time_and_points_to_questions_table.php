<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'time_limit_seconds')) {
                $table->integer('time_limit_seconds')->default(30)->after('options');
            }
            if (!Schema::hasColumn('questions', 'points')) {
                $table->integer('points')->default(100)->after('time_limit_seconds');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['time_limit_seconds', 'points']);
        });
    }
};