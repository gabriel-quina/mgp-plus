<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf')->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'is_master')) {
                $table->boolean('is_master')->default(false)->after('cpf');
            }

            // papel global (empresa). Ex.: company_coordinator, company_consultant
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable()->after('is_master');
            }

            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'is_master')) {
                $table->dropColumn('is_master');
            }

            if (Schema::hasColumn('users', 'cpf')) {
                $table->dropUnique(['cpf']);
                $table->dropColumn('cpf');
            }
        });
    }
};
