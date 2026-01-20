<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_grade_level', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('classroom_id');   // FK depois
            $table->unsignedBigInteger('grade_level_id'); // FK depois

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_grade_level');
    }
};

