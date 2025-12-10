<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // ex.: "1º Ano", "2º Ano", "3º Pré"
            $table->string('short_name')->nullable(); // ex.: "1º", "2º", "P3"
            $table->string('code')->nullable();       // ex.: "1-ano", "2-ano", "pre-3"
            $table->unsignedTinyInteger('sequence')->nullable(); // ordenação curricular
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['code']);
            $table->index('sequence');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};

