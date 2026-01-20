<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');     // FK depois
            $table->unsignedBigInteger('school_id');      // FK depois
            $table->unsignedBigInteger('grade_level_id'); // FK depois

            $table->unsignedSmallInteger('academic_year'); // ex: 2025
            $table->string('shift', 20);                   // morning|afternoon|evening

            // workflow
            $table->string('status', 30);                  // pre_enrolled|enrolled|allocated|active|...

            // transferência
            $table->string('transfer_scope', 20)->nullable(); // first|internal|external
            $table->unsignedBigInteger('origin_school_id')->nullable(); // FK depois

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

