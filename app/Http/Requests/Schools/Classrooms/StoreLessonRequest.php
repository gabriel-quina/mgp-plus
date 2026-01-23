<?php

namespace App\Http\Requests\Schools\Classrooms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorização fica no Controller (depende da escola + role + master)
    }

    public function rules(): array
    {
        return [
            'taught_at'   => ['required', 'date'],

            // Para master sem vínculo com Teacher, vamos exigir no Controller (condicional).
            'teacher_id'  => ['nullable', 'integer', 'exists:teachers,id'],

            'topic'       => ['nullable', 'string', 'max:65535'],
            'notes'       => ['nullable', 'string', 'max:65535'],

            // Pode ser vazio quando roster está vazio (Controller aceita).
            'attendances' => ['nullable', 'array'],
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'justified'])],
            'attendances.*.justification' => ['nullable', 'string', 'max:65535'],
        ];
    }
}

