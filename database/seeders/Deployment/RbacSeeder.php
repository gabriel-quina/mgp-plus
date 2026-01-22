<?php

namespace Database\Seeders\Deployment;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Permissions (mantém como está)
        $permissions = [
            'dashboard.view',
            'reports.view',
            'lessons.create',
            'lessons.update',
            'attendance.launch',
            'impersonate.user',
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p],
                ['label' => $p, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $permIdsByName = DB::table('permissions')
            ->whereIn('name', $permissions)
            ->pluck('id', 'name'); // ['dashboard.view' => 1, ...]

        // 2) Roles separadas por escopo
        // School roles (por escola, mas o cadastro do role é global)
        $schoolRoles = [
            'teacher' => ['dashboard.view', 'reports.view', 'lessons.create', 'attendance.launch'],
            'director' => ['dashboard.view', 'reports.view'],
            'secretary' => ['dashboard.view', 'reports.view'],
        ];

        // Company roles
        $companyRoles = [
            'admin' => ['dashboard.view', 'reports.view'],
            'coordinator' => ['dashboard.view', 'reports.view'],
            'consultant' => ['dashboard.view', 'reports.view'],
        ];

        // 3) Cria roles + pivots de permissions (idempotente)

        // ----- school_roles + permission_school_role -----
        foreach ($schoolRoles as $roleName => $perms) {
            DB::table('school_roles')->updateOrInsert(
                ['name' => $roleName],
                ['label' => $roleName, 'updated_at' => now(), 'created_at' => now()]
            );

            $roleId = DB::table('school_roles')->where('name', $roleName)->value('id');

            foreach ($perms as $permName) {
                $permId = $permIdsByName[$permName] ?? null;
                if (! $permId) {
                    continue;
                }

                DB::table('permission_school_role')->updateOrInsert(
                    ['permission_id' => $permId, 'school_role_id' => $roleId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // ----- company_roles + permission_company_role -----
        foreach ($companyRoles as $roleName => $perms) {
            DB::table('company_roles')->updateOrInsert(
                ['name' => $roleName],
                ['label' => $roleName, 'updated_at' => now(), 'created_at' => now()]
            );

            $roleId = DB::table('company_roles')->where('name', $roleName)->value('id');

            foreach ($perms as $permName) {
                $permId = $permIdsByName[$permName] ?? null;
                if (! $permId) {
                    continue;
                }

                DB::table('permission_company_role')->updateOrInsert(
                    ['permission_id' => $permId, 'company_role_id' => $roleId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // 4) "master" não é role. Mantemos apenas a permission no catálogo.
        // Se você quiser que alguém NÃO master possa impersonar, aí sim você cria uma company role específica
        // (ex.: company role 'superadmin') e linka 'impersonate.user' nela.
    }
}

