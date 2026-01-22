<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_scopes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id'); // PK + FK depois
            $table->string('scope', 20);           // 'company' | 'school'
            $table->timestamps();

            // garante 1 escopo por usuário (FK no constraints)
            $table->primary('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scopes');
    }
};

