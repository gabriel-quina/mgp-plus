<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->string('name', 120);
            $table->string('social_name', 120)->nullable();

            // Documentos & contato
            $table->string('cpf', 20)->nullable()->unique();   // permite NULL; único quando informado
            $table->string('email', 255)->nullable()->unique(); // idem
            $table->date('birthdate')->nullable();

            // Cor/raça (IBGE): branca, preta, parda, amarela, indígena, prefere não informar
            $table->string('race_color', 20)->nullable();

            // PcD
            $table->boolean('has_disability')->default(false);
            $table->json('disability_types')->nullable();   // ex.: ["visual","auditiva","fisica",...]
            $table->text('disability_details')->nullable();

            // Saúde & emergência
            $table->text('allergies')->nullable();
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_phone', 32)->nullable();

            $table->timestamps();

            // Índices úteis para listagem/filtro
            $table->index('name');        // busca por nome
            $table->index('race_color');  // filtros/relatórios
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

