<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('subject'); // e.g. Math, English
            $table->string('name');
            $table->string('deped_reference')->nullable();
            $table->integer('order_index')->default(0);
            $table->string('difficulty_level')->nullable(); // e.g. easy, medium, hard
            $table->text('lesson_content')->nullable();
            $table->string('lesson_resource_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
