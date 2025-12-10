<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classroom_workshop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('max_students')->nullable(); // capacidade opcional por oficina
            $table->timestamps();

            $table->unique(['classroom_id', 'workshop_id']);
            $table->index('workshop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_workshop');
    }
};

