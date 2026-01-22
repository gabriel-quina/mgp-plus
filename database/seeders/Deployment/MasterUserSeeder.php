<?php

namespace Database\Seeders\Deployment;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $user->cpf = null;
        $user->is_master = true;
        $user->must_change_password = false;

        // Idempotente e previsível: sempre aplica a senha do env
        $user->password = Hash::make($pass);
        $user->save();

        // Master precisa ter um escopo principal (você disse que só existe company|school).
        // Recomendo 'company' para cair no admin/dashboard e ter acesso transversal via is_master.
        DB::table('user_scopes')->updateOrInsert(
            ['user_id' => $user->id],
            ['scope' => 'company', 'updated_at' => now(), 'created_at' => now()]
        );
    }
}

