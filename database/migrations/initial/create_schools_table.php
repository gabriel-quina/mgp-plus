<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('city_id'); // FK depois (constraints)

            $table->string('name');
            $table->boolean('is_historical')->default(false);

            // endereço (tudo opcional)
            $table->string('street')->nullable();
            $table->string('number')->nullable();       // string pq pode ser "s/n", "12A" etc
            $table->string('neighborhood')->nullable();
            $table->string('complement')->nullable();
            $table->string('cep', 9)->nullable();       // "00000-000"

            // usado no seu accessibleSchools(): municipal/state
            $table->string('administrative_dependency', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

