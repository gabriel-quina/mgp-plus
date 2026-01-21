<?php

namespace Database\Seeders\Deployment;

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

        // Master não tem CPF (fica NULL)
        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->cpf = null;
        $user->is_master = true;
        $user->must_change_password = false;

        // Mantém senha do env (idempotente e previsível em implantação)
        $user->password = Hash::make($pass);

        $user->save();
    }
}

