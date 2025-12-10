<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // Dados pessoais (Person é abstrata; guardamos aqui)
            $table->string('name', 150);
            $table->string('social_name', 150)->nullable();

            // CPF será normalizado (apenas dígitos) pelo mutator em Person/Teacher
            $table->string('cpf', 20)->nullable()->unique();

            $table->string('email', 150)->nullable()->unique();
            $table->date('birthdate')->nullable();

            // Estado do cadastro
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices úteis para buscas
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

