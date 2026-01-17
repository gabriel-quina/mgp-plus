<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_id')->index();

            $table->string('administrative_dependency')
                ->default('municipal');
            // municipal | state | private | federal (se quiser depois)

            $table->string('name', 150)->index();

            // Flag para escolas cadastradas apenas para histórico de origem de matrícula
            $table->boolean('is_historical')->default(false)->index();

            // Endereço (opcional)
            $table->string('street', 150)->nullable();      // logradouro
            $table->string('number', 20)->nullable();       // número (string p/ "s/n")
            $table->string('neighborhood', 120)->nullable();// bairro
            $table->string('complement', 120)->nullable();  // complemento
            $table->string('cep', 9)->nullable();           // 12345-678

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
