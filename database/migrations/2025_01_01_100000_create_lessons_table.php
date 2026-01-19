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
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->dateTime('lesson_at');
            $table->string('topic')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->index(['classroom_id', 'lesson_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
