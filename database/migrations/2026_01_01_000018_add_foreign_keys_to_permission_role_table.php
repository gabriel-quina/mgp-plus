<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('permission_role__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id');
                $table->foreignId('permission_id');
                $table->timestamps();


                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();
                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO permission_role__tmp (
                    id, role_id, permission_id, created_at, updated_at
                )
                SELECT
                    id, role_id, permission_id, created_at, updated_at
                FROM permission_role
            ");

            Schema::drop('permission_role');
            Schema::rename('permission_role__tmp', 'permission_role');

            DB::statement("
                CREATE INDEX permission_role_role_id_index
                ON permission_role (role_id)
            ");
            DB::statement("
                CREATE INDEX permission_role_permission_id_index
                ON permission_role (permission_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX permission_role_role_id_permission_id_unique
                ON permission_role (role_id, permission_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('permission_role', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('permission_role__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id');
                $table->foreignId('permission_id');
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO permission_role__tmp (
                    id, role_id, permission_id, created_at, updated_at
                )
                SELECT
                    id, role_id, permission_id, created_at, updated_at
                FROM permission_role
            ");

            Schema::drop('permission_role');
            Schema::rename('permission_role__tmp', 'permission_role');

            DB::statement("
                CREATE INDEX permission_role_role_id_index
                ON permission_role (role_id)
            ");
            DB::statement("
                CREATE INDEX permission_role_permission_id_index
                ON permission_role (permission_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX permission_role_role_id_permission_id_unique
                ON permission_role (role_id, permission_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
        });
    }
};
