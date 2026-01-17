<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_city_access', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->index();

            $table->foreignId('city_id')
                ->index();

            $table->unique(['teacher_id', 'city_id']);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_city_access');
    }
};
