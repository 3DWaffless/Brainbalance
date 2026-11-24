<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_activity_logs')) {
            Schema::create('student_activity_logs', function (Blueprint $table) {$table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();$table->string('action');
                $table->string('category')->default('general');$table->text('description');
                $table->json('metadata')->nullable();$table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();$table->timestamps();

                $table->index(['user_id', 'created_at']);$table->index(['category', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_logs');
    }
};