<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();

            // Grupo (Turma PAI ou Subturma)
            $table->foreignId('classroom_id')
                ->constrained()
                ->cascadeOnDelete();

            // Oficina
            $table->foreignId('workshop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');              // "Prova 1", "Trabalho em grupo", etc.
            $table->text('description')->nullable();
            $table->date('due_at')->nullable();   // data da avaliação / entrega

            // Escala: 'points' ou 'concept'
            $table->string('scale_type', 20)->default('points');

            // ✅ Pontuação máxima agora 0–100 (com 2 casas decimais)
            $table->decimal('max_points', 5, 2)->default(100.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
