<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'reports.view',
            'lessons.create',
            'lessons.update',
            'attendance.launch',
            'impersonate.user',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p], ['label' => $p]);
        }

        $roles = [
            // escola
            'school_teacher' => ['dashboard.view', 'reports.view', 'lessons.create', 'attendance.launch'],
            'school_director' => ['dashboard.view', 'reports.view'],
            'school_secretary' => ['dashboard.view', 'reports.view'],

            // cidade
            'city_education_secretary' => ['dashboard.view', 'reports.view'],

            // empresa
            'company_coordinator' => ['dashboard.view', 'reports.view'],
            'company_consultant' => ['dashboard.view', 'reports.view'],

            // master (se quiser mapear também)
            'master' => ['impersonate.user'],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName], ['label' => $roleName]);

            $permModels = Permission::whereIn('name', $perms)->get();
            $role->permissions()->syncWithoutDetaching($permModels->pluck('id')->all());
        }
    }
}
