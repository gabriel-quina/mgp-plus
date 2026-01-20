<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_grades', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('assessment_id');         // FK depois
            $table->unsignedBigInteger('student_enrollment_id'); // FK depois

            // Use um ou outro (dependendo do Assessment->scale_type)
            $table->decimal('score_points', 6, 1)->nullable();
            $table->string('score_concept', 30)->nullable(); // ruim|regular|bom|muito_bom|excelente etc

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_grades');
    }
};

