<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_assignments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // FK depois
            $table->unsignedBigInteger('role_id'); // FK depois

            // escopo polimórfico (School, City, State) ou global (null)
            $table->string('scope_type')->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
    }
};

