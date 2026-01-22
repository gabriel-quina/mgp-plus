<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_company_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id');    // FK depois
            $table->unsignedBigInteger('company_role_id');  // FK depois
            $table->timestamps();
        });

        Schema::create('permission_school_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id');   // FK depois
            $table->unsignedBigInteger('school_role_id');  // FK depois
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_school_role');
        Schema::dropIfExists('permission_company_role');
    }
};

