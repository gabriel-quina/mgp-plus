<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');   // ex: company_admin, company_consultant
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('school_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');   // ex: teacher, principal
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_roles');
        Schema::dropIfExists('company_roles');
    }
};

