<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('MASTER_EMAIL', 'master@uptakeeducation.com.br');
        $name  = env('MASTER_NAME', 'Master UPTAKE');
        $pass  = env('MASTER_PASSWORD', 'MGPplus@$$1403');

        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->is_master = true;
        $user->must_change_password = false;

        $user->password = Hash::make($pass);

        $user->save();
    }
}

