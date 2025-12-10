<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_workshop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'workshop_id']);
            $table->index('school_id');
            $table->index('workshop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_workshop');
    }
};

