<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
        // O parâmetro de rota é {teacher}; pode ser o ID ou o model via binding.
        $routeTeacher = $this->route('teacher');
        $teacherId = is_object($routeTeacher) ? $routeTeacher->id : $routeTeacher;

        return [
            'name'        => ['required', 'string', 'max:150'],
            'social_name' => ['nullable', 'string', 'max:150'],
            'cpf'         => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('teachers', 'cpf')->ignore($teacherId),
            ],
            'email'       => [
                'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique('teachers', 'email')->ignore($teacherId),
            ],
            'birthdate'   => ['nullable', 'date', 'before_or_equal:today'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        // Anti-duplicidade entre papéis (mantemos o aviso no update também)
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

