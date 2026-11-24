<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;
    
    public function up(): void
    {
        if (!Schema::hasTable('module_sections')) {
            Schema::create('module_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->text('notes')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->index(['class_id', 'position']);
            });
        }

        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                if (!Schema::hasColumn('modules', 'class_id')) {
                    $table->foreignId('class_id')->nullable()->after('id')->constrained('classes')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('modules', 'module_section_id')) {
                    $table->foreignId('module_section_id')->nullable()->after('class_id')->constrained('module_sections')->nullOnDelete();
                }
                if (!Schema::hasColumn('modules', 'description')) {
                    $table->text('description')->nullable()->after('title');
                }
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                if (!Schema::hasColumn('announcements', 'module_section_id')) {
                    $table->foreignId('module_section_id')->nullable()->after('class_id')->constrained('module_sections')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('student_activity_logs')) {
            Schema::table('student_activity_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('student_activity_logs', 'class_id')) {
                    $table->foreignId('class_id')->nullable()->after('user_id')->constrained('classes')->cascadeOnDelete();
                    $table->index(['class_id', 'created_at']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_activity_logs') && Schema::hasColumn('student_activity_logs', 'class_id')) {
            Schema::table('student_activity_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('class_id');
            });
        }

        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'module_section_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('module_section_id');
            });
        }

        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                if (Schema::hasColumn('modules', 'module_section_id')) {
                    $table->dropConstrainedForeignId('module_section_id');
                }
            });
        }

        Schema::dropIfExists('module_sections');
    }
};