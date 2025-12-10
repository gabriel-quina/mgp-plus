<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_enrollment_id')
                ->constrained()
                ->cascadeOnDelete();

            // Por enquanto algo simples: presente ou ausente
            $table->boolean('present')->default(true);

            // Depois você pode trocar por um enum/status mais sofisticado
            $table->string('justification')->nullable();

            $table->timestamps();

            $table->unique(['lesson_id', 'student_enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_attendances');
    }
};
