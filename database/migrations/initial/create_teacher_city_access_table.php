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

            $table->unsignedBigInteger('teacher_id'); // FK depois
            $table->unsignedBigInteger('city_id');    // FK depois

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_city_access');
    }
};

