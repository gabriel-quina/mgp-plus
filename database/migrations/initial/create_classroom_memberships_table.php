<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_memberships', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_enrollment_id'); // FK depois
            $table->unsignedBigInteger('classroom_id');          // FK depois

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_memberships');
    }
};

