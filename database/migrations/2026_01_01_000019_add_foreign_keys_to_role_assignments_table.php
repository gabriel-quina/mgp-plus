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

            Schema::create('role_assignments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('role_id');
                $table->nullableMorphs('scope');
                $table->timestamps();


                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO role_assignments__tmp (
                    id, user_id, role_id, scope_type, scope_id,
                    created_at, updated_at
                )
                SELECT
                    id, user_id, role_id, scope_type, scope_id,
                    created_at, updated_at
                FROM role_assignments
            ");

            Schema::drop('role_assignments');
            Schema::rename('role_assignments__tmp', 'role_assignments');

            DB::statement("
                CREATE INDEX role_assignments_user_id_index
                ON role_assignments (user_id)
            ");
            DB::statement("
                CREATE INDEX role_assignments_role_id_index
                ON role_assignments (role_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX role_assignments_unique
                ON role_assignments (user_id, role_id, scope_type, scope_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('role_assignments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('role_id');
                $table->nullableMorphs('scope');
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO role_assignments__tmp (
                    id, user_id, role_id, scope_type, scope_id,
                    created_at, updated_at
                )
                SELECT
                    id, user_id, role_id, scope_type, scope_id,
                    created_at, updated_at
                FROM role_assignments
            ");

            Schema::drop('role_assignments');
            Schema::rename('role_assignments__tmp', 'role_assignments');

            DB::statement("
                CREATE INDEX role_assignments_user_id_index
                ON role_assignments (user_id)
            ");
            DB::statement("
                CREATE INDEX role_assignments_role_id_index
                ON role_assignments (role_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX role_assignments_unique
                ON role_assignments (user_id, role_id, scope_type, scope_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['role_id']);
        });
    }
};
