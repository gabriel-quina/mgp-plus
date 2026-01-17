<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->index();

            $table->foreignId('school_id')
                ->index();

            // Opcional, só para "saber qual vínculo financia"
            $table->foreignId('engagement_id')
                ->nullable()
                ->index();

            $table->unsignedSmallInteger('academic_year'); // ex.: 2025
            // morning | afternoon | evening (validado no Request)
            $table->string('shift', 16)->nullable();

            $table->unsignedTinyInteger('hours_per_week')->nullable(); // 1..44 (validado no Request)
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            // Evita duplicar a mesma alocação para ano/turno
            $table->unique(['teacher_id', 'school_id', 'academic_year', 'shift']);

            // Índices de filtros comuns
            $table->index(['teacher_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
