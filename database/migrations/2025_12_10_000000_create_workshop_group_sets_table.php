<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_group_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('workshop_id')
                ->constrained('workshops')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('academic_year');
            $table->string('shift', 50);
            $table->string('grade_levels_signature');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_group_sets');
    }
};
