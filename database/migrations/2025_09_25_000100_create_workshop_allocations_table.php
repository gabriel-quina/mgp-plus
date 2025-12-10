<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_allocations', function (Blueprint $table) {
            $table->id();

            // Subturma (sempre child de uma turma PAI)
            $table->foreignId('child_classroom_id')
                ->constrained('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Oficina (malha independente por oficina)
            $table->foreignId('workshop_id')
                ->constrained('workshops')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Episódio de matrícula do aluno no ano letivo (verdade-fonte)
            $table->foreignId('student_enrollment_id')
                ->constrained('student_enrollments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Preserva ajustes manuais em rebalanceamentos
            $table->boolean('is_locked')->default(false);

            // Opcional: auditoria simples
            $table->string('note', 300)->nullable();

            $table->timestamps();

            // Regras de unicidade/consistência:
            // 1) Aluno não pode estar duplicado na MESMA subturma/oficina:
            $table->unique(
                ['child_classroom_id', 'workshop_id', 'student_enrollment_id'],
                'wk_alloc_unique_in_child'
            );

            // 2) Aluno não pode aparecer em DUAS subturmas diferentes da MESMA oficina:
            $table->unique(
                ['workshop_id', 'student_enrollment_id'],
                'wk_alloc_unique_per_workshop'
            );

            $table->index('child_classroom_id');
            $table->index('workshop_id');
            $table->index('student_enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_allocations');
    }
};
