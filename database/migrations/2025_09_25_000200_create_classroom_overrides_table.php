<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_overrides', function (Blueprint $table) {
            $table->id();

            // Episódio de matrícula do aluno (usado para saber o ano letivo e filtrar pertença)
            $table->foreignId('student_enrollment_id')
                ->constrained('student_enrollments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Turma PAI de origem e destino (mesma escola/turno/ano) — checado na aplicação
            $table->foreignId('from_classroom_id')
                ->constrained('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('to_classroom_id')
                ->constrained('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Ativo (permite histórico: desativar ao reverter decisão)
            $table->boolean('is_active')->default(true);

            // Auditoria
            $table->string('reason', 300)->nullable();
            $table->timestamps();

            // Um override ativo por aluno/ano letivo (via episódio):
            $table->unique(
                ['student_enrollment_id', 'is_active'],
                'one_active_override_per_year'
            );

            // Índices auxiliares
            $table->index('from_classroom_id');
            $table->index('to_classroom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_overrides');
    }
};
