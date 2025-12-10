<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Escola de DESTINO
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Série / nível escolar
            $table->foreignId('grade_level_id')
                ->constrained('grade_levels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Ano letivo (ex.: 2025)
            $table->unsignedSmallInteger('academic_year');

            // Turno
            $table->enum('shift', ['morning', 'afternoon', 'evening'])
                  ->default('morning');

            // Escopo: first | internal | external
            $table->enum('transfer_scope', ['first', 'internal', 'external'])
                  ->default('first');

            // Escola de ORIGEM (pode ser histórica)
            $table->foreignId('origin_school_id')
                ->nullable()
                ->constrained('schools')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // Janela do episódio
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            // Status acadêmico
            $table->enum('status', [
                'active',
                'completed',
                'failed',
                'transferred',
                'dropped',
                'suspended',
            ])->default('active');

            $table->timestamps();

            // Índices para consultas reais
            $table->index(['student_id', 'status'], 'idx_se_student_status');
            $table->index(['school_id', 'academic_year', 'shift', 'status'], 'idx_se_school_year_shift_status');
            $table->index('origin_school_id', 'idx_se_origin_school');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

