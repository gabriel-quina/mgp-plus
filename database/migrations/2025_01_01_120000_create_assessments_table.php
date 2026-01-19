<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->dateTime('assessment_at');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('scale_type', 20);
            $table->decimal('max_points', 5, 1)->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'assessment_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
