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

            $table->unsignedBigInteger('teacher_id'); // FK depois

            $table->string('engagement_type', 30);   // our_clt|our_pj|our_temporary|municipal...
            $table->unsignedInteger('hours_per_week')->nullable();

            $table->string('status', 20)->nullable(); // active|inactive etc (você define)

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedBigInteger('city_id')->nullable(); // FK depois
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_engagements');
    }
};

