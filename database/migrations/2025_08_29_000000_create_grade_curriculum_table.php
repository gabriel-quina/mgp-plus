<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_curriculum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->index();
            $table->string('academic_period', 32); // ex.: 2025.2
            $table->foreignId('grade_level_id')->index();
            $table->foreignId('workshop_id')->index();
            $table->timestamps();

            $table->unique(['school_id','academic_period','grade_level_id','workshop_id'], 'uniq_grade_curriculum');
            $table->index(['school_id','academic_period']);
            $table->index(['grade_level_id','academic_period']);
            $table->index(['workshop_id','academic_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_curriculum');
    }
};
