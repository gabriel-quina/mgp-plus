<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classroom_grade_level', function (Blueprint $table) {
            $table->id();

            $table->foreignId('classroom_id')
                  ->constrained('classrooms')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('grade_level_id')
                  ->constrained('grade_levels')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['classroom_id','grade_level_id'], 'uniq_classroom_gradelevel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_grade_level');
    }
};

