<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_workshops', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');   // FK depois (constraints)
            $table->unsignedBigInteger('workshop_id'); // FK depois (constraints)

            $table->date('starts_at');
            $table->date('ends_at')->nullable();

            $table->string('status', 20)->default('active'); // active|inactive|expired

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_workshops');
    }
};

