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
        $name = env('MASTER_NAME', 'Master UPTAKE');
        $pass = env('MASTER_PASSWORD', 'MGPplus@$$1403');

        $cpf = env('MASTER_CPF', null);
        $cpf = is_string($cpf) && trim($cpf) !== '' ? preg_replace('/\D+/', '', $cpf) : null;

        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->cpf = $cpf;
        $user->is_master = true;
        $user->must_change_password = false;

        // Garante password para NOT NULL
        if (! $user->exists) {
            $user->password = Hash::make($pass);
        } else {
            // opcional: sempre manter senha do env
            $user->password = Hash::make($pass);
        }

        $user->save();
    }
}
