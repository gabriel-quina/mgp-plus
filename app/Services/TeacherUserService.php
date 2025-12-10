<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherUserService
{
    public function syncFromTeacher(Teacher $teacher): ?User
    {
        if (! $teacher->cpf) {
            return null;
        }

        $cpf = $teacher->cpf;

        $user = User::firstOrNew(['cpf' => $cpf]);

        $user->name = $teacher->name;

        /**
         * Email:
         * - Atualiza se o teacher tiver email preenchido.
         * - Não apaga email do user se vier vazio.
         */
        if (! empty($teacher->email)) {
            $user->email = $teacher->email;
        }

        /**
         * is_master:
         * - Nunca rebaixa automaticamente.
         * - Se é novo, fica false.
         */
        if (! $user->exists) {
            $user->is_master = false;
        }

        // password é NOT NULL no seu schema
        if (! $user->exists || empty($user->password)) {
            $user->password = Hash::make(substr($cpf, 0, 6));
            $user->must_change_password = true;
        }

        $user->save();

        return $user;
    }
}
