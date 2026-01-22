<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========= user_scopes =========
        Schema::table('user_scopes', function (Blueprint $table) {
            $table->index('scope', 'idx_user_scopes_scope');

            $table->foreign('user_id', 'fk_user_scopes_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });

        // check constraint (MariaDB suporta)
        DB::statement("ALTER TABLE user_scopes
            ADD CONSTRAINT chk_user_scopes_scope
            CHECK (scope IN ('company','school'))");

        // ========= roles =========
        Schema::table('company_roles', function (Blueprint $table) {
            $table->unique('name', 'uq_company_roles_name');
        });

        Schema::table('school_roles', function (Blueprint $table) {
            $table->unique('name', 'uq_school_roles_name');
        });

        // ========= company_role_assignments =========
        Schema::table('company_role_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'company_role_id'], 'uq_company_role_assignments_pair');

            $table->index('user_id', 'idx_company_role_assignments_user_id');
            $table->index('company_role_id', 'idx_company_role_assignments_role_id');

            $table->foreign('user_id', 'fk_company_role_assignments_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('company_role_id', 'fk_company_role_assignments_role_id')
                ->references('id')->on('company_roles')
                ->cascadeOnDelete();
        });

        // ========= school_role_assignments =========
        Schema::table('school_role_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'school_role_id', 'school_id'], 'uq_school_role_assignments_triplet');

            $table->index('user_id', 'idx_school_role_assignments_user_id');
            $table->index('school_role_id', 'idx_school_role_assignments_role_id');
            $table->index('school_id', 'idx_school_role_assignments_school_id');

            $table->foreign('user_id', 'fk_school_role_assignments_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('school_role_id', 'fk_school_role_assignments_role_id')
                ->references('id')->on('school_roles')
                ->cascadeOnDelete();

            $table->foreign('school_id', 'fk_school_role_assignments_school_id')
                ->references('id')->on('schools')
                ->restrictOnDelete();
        });

        // ========= company_school_access_grants =========
        Schema::table('company_school_access_grants', function (Blueprint $table) {
            $table->unique(['user_id', 'school_id'], 'uq_company_school_access_grants_pair');

            $table->index('user_id', 'idx_company_school_access_grants_user_id');
            $table->index('school_id', 'idx_company_school_access_grants_school_id');

            $table->foreign('user_id', 'fk_company_school_access_grants_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('school_id', 'fk_company_school_access_grants_school_id')
                ->references('id')->on('schools')
                ->restrictOnDelete();
        });

        // ========= permission pivots =========
        Schema::table('permission_company_role', function (Blueprint $table) {
            $table->unique(['permission_id', 'company_role_id'], 'uq_permission_company_role_pair');
            $table->index('company_role_id', 'idx_permission_company_role_role_id');

            $table->foreign('permission_id', 'fk_permission_company_role_permission_id')
                ->references('id')->on('permissions')
                ->cascadeOnDelete();

            $table->foreign('company_role_id', 'fk_permission_company_role_role_id')
                ->references('id')->on('company_roles')
                ->cascadeOnDelete();
        });

        Schema::table('permission_school_role', function (Blueprint $table) {
            $table->unique(['permission_id', 'school_role_id'], 'uq_permission_school_role_pair');
            $table->index('school_role_id', 'idx_permission_school_role_role_id');

            $table->foreign('permission_id', 'fk_permission_school_role_permission_id')
                ->references('id')->on('permissions')
                ->cascadeOnDelete();

            $table->foreign('school_role_id', 'fk_permission_school_role_role_id')
                ->references('id')->on('school_roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Atenção: seu monolito não remove check constraints também.
        // Aqui eu removo só as FKs/índices/uniques.

        Schema::table('permission_school_role', function (Blueprint $table) {
            $table->dropForeign('fk_permission_school_role_permission_id');
            $table->dropForeign('fk_permission_school_role_role_id');
            $table->dropUnique('uq_permission_school_role_pair');
            $table->dropIndex('idx_permission_school_role_role_id');
        });

        Schema::table('permission_company_role', function (Blueprint $table) {
            $table->dropForeign('fk_permission_company_role_permission_id');
            $table->dropForeign('fk_permission_company_role_role_id');
            $table->dropUnique('uq_permission_company_role_pair');
            $table->dropIndex('idx_permission_company_role_role_id');
        });

        Schema::table('company_school_access_grants', function (Blueprint $table) {
            $table->dropForeign('fk_company_school_access_grants_user_id');
            $table->dropForeign('fk_company_school_access_grants_school_id');
            $table->dropUnique('uq_company_school_access_grants_pair');
            $table->dropIndex('idx_company_school_access_grants_user_id');
            $table->dropIndex('idx_company_school_access_grants_school_id');
        });

        Schema::table('school_role_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_school_role_assignments_user_id');
            $table->dropForeign('fk_school_role_assignments_role_id');
            $table->dropForeign('fk_school_role_assignments_school_id');

            $table->dropUnique('uq_school_role_assignments_triplet');
            $table->dropIndex('idx_school_role_assignments_user_id');
            $table->dropIndex('idx_school_role_assignments_role_id');
            $table->dropIndex('idx_school_role_assignments_school_id');
        });

        Schema::table('company_role_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_company_role_assignments_user_id');
            $table->dropForeign('fk_company_role_assignments_role_id');

            $table->dropUnique('uq_company_role_assignments_pair');
            $table->dropIndex('idx_company_role_assignments_user_id');
            $table->dropIndex('idx_company_role_assignments_role_id');
        });

        Schema::table('school_roles', function (Blueprint $table) {
            $table->dropUnique('uq_school_roles_name');
        });

        Schema::table('company_roles', function (Blueprint $table) {
            $table->dropUnique('uq_company_roles_name');
        });

        Schema::table('user_scopes', function (Blueprint $table) {
            $table->dropForeign('fk_user_scopes_user_id');
            $table->dropIndex('idx_user_scopes_scope');
        });
    }
};

