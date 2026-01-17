<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_engagements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->index();

            // our_clt | our_pj | our_temporary | municipal
            $table->string('engagement_type', 32);
            $table->unsignedTinyInteger('hours_per_week'); // 1..44 (validado no Request)
            // active | suspended | ended
            $table->string('status', 24)->default('active');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Somente quando engagement_type = 'municipal'
            $table->foreignId('city_id')
                ->nullable()
                ->index();

            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['engagement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_engagements');
    }
};
