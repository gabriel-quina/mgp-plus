<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('classroom_id'); // FK depois
            $table->unsignedBigInteger('teacher_id');   // FK depois

            $table->date('taught_at');

            $table->string('topic')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_locked')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

