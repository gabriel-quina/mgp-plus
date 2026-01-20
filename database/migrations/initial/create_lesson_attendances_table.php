<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lesson_id');             // FK depois
            $table->unsignedBigInteger('student_enrollment_id'); // FK depois

            $table->boolean('present')->default(true);
            $table->text('justification')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_attendances');
    }
};

