<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_group_set_grade_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_group_set_id')
                ->index();
            $table->foreignId('grade_level_id')
                ->index();
            $table->timestamps();

            $table->unique(['workshop_group_set_id', 'grade_level_id'], 'uniq_wgs_grade_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_group_set_grade_level');
    }
};
