<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->integer('academic_year_id');
            $table->string('shift', 20);
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->json('grade_level_ids');
            $table->string('grades_signature');
            $table->integer('group_number');
            $table->integer('capacity_hint')->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();

            $table->unique([
                'school_id',
                'academic_year_id',
                'shift',
                'workshop_id',
                'grades_signature',
                'group_number',
            ], 'classrooms_unique_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
