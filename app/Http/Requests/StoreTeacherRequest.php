<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cpf = $this->input('cpf');

        $this->merge([
            'name'        => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'social_name' => is_string($this->input('social_name')) ? trim($this->input('social_name')) : $this->input('social_name'),
            'email'       => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'cpf'         => is_string($cpf) ? preg_replace('/\D+/', '', $cpf) : $cpf,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'social_name' => ['nullable', 'string', 'max:150'],
            'cpf'         => ['nullable', 'string', 'max:20', 'unique:teachers,cpf'],
            'email'       => ['nullable', 'string', 'email', 'max:150', 'unique:teachers,email'],
            'birthdate'   => ['nullable', 'date', 'before_or_equal:today'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        // Anti-duplicidade entre papéis (opcional mas recomendado):
        // se o CPF informado já existir em students, bloquear para evitar "mesma pessoa" duplicada.
        $validator->after(function ($v) {
            $cpf = $this->input('cpf');
            if ($cpf) {
                $existsInStudents = DB::table('students')->where('cpf', $cpf)->exists();
                if ($existsInStudents) {
                    $v->errors()->add('cpf', 'Já existe um aluno com este CPF. Use o cadastro existente para não duplicar pessoas.');
                }
            }

            $email = $this->input('email');
            if ($email) {
                $existsInStudentsEmail = DB::table('students')->where('email', $email)->exists();
                if ($existsInStudentsEmail) {
                    $v->errors()->add('email', 'Já existe um aluno com este e-mail. Use o cadastro existente para não duplicar pessoas.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name'        => 'nome',
            'social_name' => 'nome social',
            'cpf'         => 'CPF',
            'email'       => 'e-mail',
            'birthdate'   => 'data de nascimento',
            'is_active'   => 'ativo',
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique'   => 'Já existe um professor com este CPF.',
            'email.unique' => 'Já existe um professor com este e-mail.',
        ];
    }
}

