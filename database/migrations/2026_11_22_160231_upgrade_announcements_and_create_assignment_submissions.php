<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'type')) {
                $table->string('type', 30)->default('text')->after('teacher_id');
            }
            if (!Schema::hasColumn('announcements', 'title')) {
                $table->string('title')->nullable()->after('type');
            }
            if (!Schema::hasColumn('announcements', 'file_path')) {
                $table->string('file_path')->nullable()->after('content');
            }
            if (!Schema::hasColumn('announcements', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('announcements', 'file_type')) {
                $table->string('file_type', 30)->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('announcements', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }
            if (!Schema::hasColumn('announcements', 'video_url')) {
                $table->string('video_url')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('announcements', 'quiz_id')) {
                $table->unsignedBigInteger('quiz_id')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('announcements', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('quiz_id');
            }
            if (!Schema::hasColumn('announcements', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('module_id');
            }
            if (!Schema::hasColumn('announcements', 'points')) {
                $table->integer('points')->default(100)->after('due_at');
            }
        });

        if (!Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->text('answer_text')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->integer('score')->nullable();
                $table->text('teacher_feedback')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['announcement_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');

        Schema::table('announcements', function (Blueprint $table) {
            $cols = ['type', 'title', 'file_path', 'file_name', 'file_type', 'file_size', 'video_url', 'quiz_id', 'module_id', 'due_at', 'points'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('announcements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};