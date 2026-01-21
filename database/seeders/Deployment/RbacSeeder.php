<?php

namespace Database\Seeders\Deployment;

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
            'school_teacher'          => ['dashboard.view', 'reports.view', 'lessons.create', 'attendance.launch'],
            'school_director'         => ['dashboard.view', 'reports.view'],
            'school_secretary'        => ['dashboard.view', 'reports.view'],
            'city_education_secretary'=> ['dashboard.view', 'reports.view'],
            'company_coordinator'     => ['dashboard.view', 'reports.view'],
            'company_consultant'      => ['dashboard.view', 'reports.view'],
            'master'                  => ['impersonate.user'],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName], ['label' => $roleName]);

            $permIds = Permission::whereIn('name', $perms)->pluck('id')->all();

            // Constraints garantem unicidade no pivot; isso mantém idempotência
            $role->permissions()->syncWithoutDetaching($permIds);
        }
    }
}

