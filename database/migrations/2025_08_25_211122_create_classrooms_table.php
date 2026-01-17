<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();

            // Chaves de escopo
            $table->foreignId('school_id')
                  ->index();

            // Período letivo ANUAL (ex.: 2025).
            // Sem default no banco; o "padrão (ano corrente)" você aplica no form/controller.
            $table->unsignedSmallInteger('academic_year')->nullable();

            // Hierarquia (subturma)
            $table->foreignId('parent_classroom_id')
                  ->nullable()
                  ->index();

            $table->foreignId('workshop_id')
                  ->nullable()
                  ->index();
            $table->foreignId('workshop_group_set_id')
                  ->nullable()
                  ->index();
            $table->unsignedInteger('group_number')
                  ->nullable();

            // Identificação
            $table->string('name', 150);
            $table->enum('shift', ['morning','afternoon','evening']); // manhã/tarde/noite
            $table->boolean('is_active')->default(true);

            // Conjunto canônico de anos atendidos (ex.: "1,2,3")
            // -> gerado a partir do multiselect de grade_levels (ordenado e único).
            $table->string('grade_level_key', 191)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            // Regra de unicidade:
            // 1 turma por (escola, academic_year, shift, conjunto de anos)
            $table->unique(
                ['school_id', 'academic_year', 'shift', 'grade_level_key'],
                'uniq_class_school_year_shift_set'
            );

            // Índices auxiliares
            $table->index(['school_id', 'academic_year'], 'idx_class_school_year');
            $table->index(['shift', 'is_active'], 'idx_class_shift_active');
            $table->index(['workshop_group_set_id', 'group_number'], 'idx_classroom_group_set_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
