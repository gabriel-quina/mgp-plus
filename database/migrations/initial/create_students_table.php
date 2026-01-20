<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Person base
            $table->string('name');
            $table->string('cpf', 11)->nullable();
            $table->string('email')->nullable();
            $table->date('birthdate')->nullable();

            // Student extras
            $table->string('race_color')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->json('disability_types')->nullable();
            $table->text('disability_details')->nullable();
            $table->text('allergies')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

