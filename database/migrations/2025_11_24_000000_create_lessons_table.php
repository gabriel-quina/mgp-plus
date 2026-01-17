<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            // Grupo onde a aula acontece (pode ser Turma PAI ou Subturma)
            $table->foreignId('classroom_id')
                ->index();

            // Oficina dessa aula (sempre 1 oficina por grupo)
            $table->foreignId('workshop_id')
                ->index();

            // Dados básicos da aula
            $table->date('taught_at');              // data da aula
            $table->string('topic')->nullable();    // assunto / conteúdo
            $table->text('notes')->nullable();      // observações livres

            // Trava futura, se você quiser “fechar” a aula
            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            // Evita duplicar aula no mesmo horário no mesmo grupo (opcional)
            $table->unique(
                ['classroom_id', 'workshop_id', 'taught_at', 'starts_at'],
                'lessons_unique_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
