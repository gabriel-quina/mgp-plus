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
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['school_id', 'workshop_id']);
            $table->index(['school_id', 'status']);
            $table->index('school_id');
            $table->index('workshop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_workshop');
    }
};
