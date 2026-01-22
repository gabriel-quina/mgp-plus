<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');          // FK depois
            $table->unsignedBigInteger('company_role_id');  // FK depois
            $table->timestamps();
        });

        Schema::create('school_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');         // FK depois
            $table->unsignedBigInteger('school_role_id');  // FK depois
            $table->unsignedBigInteger('school_id');       // FK depois
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_role_assignments');
        Schema::dropIfExists('company_role_assignments');
    }
};

