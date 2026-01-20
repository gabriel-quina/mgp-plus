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

            $table->unsignedBigInteger('school_id');          // FK depois
            $table->unsignedSmallInteger('academic_year');    // 2025, 2026...
            $table->string('shift', 20);                      // morning|afternoon|evening

            $table->unsignedBigInteger('school_workshop_id'); // FK depois (contrato)

            $table->string('grades_signature', 255);          // "1,2" (ids ordenados)
            $table->unsignedInteger('group_number')->default(1);

            $table->unsignedInteger('capacity_hint')->nullable();
            $table->string('status', 30)->nullable();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};

