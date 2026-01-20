<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('teacher_id');    // FK depois
            $table->unsignedBigInteger('school_id');     // FK depois
            $table->unsignedBigInteger('engagement_id')->nullable(); // FK depois

            $table->unsignedSmallInteger('academic_year');

            $table->unsignedInteger('hours_per_week')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};

