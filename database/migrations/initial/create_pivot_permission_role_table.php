<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('permission_id'); // FK depois
            $table->unsignedBigInteger('role_id');       // FK depois

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};

