<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('classroom_student');
    }

    public function down(): void
    {
        // Intentionally empty: the table was an unused duplicate of class_student.
    }
};
?>
