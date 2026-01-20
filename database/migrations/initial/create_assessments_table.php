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

            $table->unsignedBigInteger('classroom_id'); // FK depois

            $table->string('title');
            $table->text('description')->nullable();

            // sua regra: data em que o teste foi aplicado
            $table->date('due_at');

            // suporte a pontos e/ou conceito (você decide no request)
            $table->string('scale_type', 20)->nullable();   // points|concept|null
            $table->decimal('max_points', 6, 1)->nullable(); // ex: 10.0

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};

