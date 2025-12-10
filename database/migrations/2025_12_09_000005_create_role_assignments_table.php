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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            // escopo polimórfico:
            // null = global
            // School, City, (State futuramente)
            $table->nullableMorphs('scope');

            $table->timestamps();

            $table->unique(['user_id', 'role_id', 'scope_type', 'scope_id'], 'role_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
    }
};
