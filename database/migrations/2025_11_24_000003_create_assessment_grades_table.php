<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_grades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_enrollment_id')
                ->constrained()
                ->cascadeOnDelete();

            // ✅ Nota numérica de 0 a 100 (2 casas decimais)
            $table->decimal('score_points', 5, 2)->nullable();

            // Conceito (se você estiver usando)
            $table->string('score_concept', 20)->nullable();

            $table->text('notes')->nullable(); // observação opcional da nota

            $table->timestamps();

            $table->unique(['assessment_id', 'student_enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_grades');
    }
};
