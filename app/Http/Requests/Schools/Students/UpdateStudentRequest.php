<?php

namespace App\Http\Requests\Schools\Students;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentParam = $this->route('student');

        // Pode ser Model (route-model-binding) ou ID (string/int)
        $id = is_object($studentParam) ? ($studentParam->id ?? null) : $studentParam;
        $id = is_numeric($id) ? (int) $id : null;

        return [
            // STUDENT (coerente com seu _form atual: não tem social_name)
            'student.name' => ['required', 'string', 'max:120'],
            'student.cpf' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('students', 'cpf')->ignore($id),
            ],
            'student.email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($id),
            ],

            'student.birthdate' => ['nullable', 'date'],
            'student.race_color' => ['nullable', 'string', 'max:20'],

            'student.has_disability' => ['nullable', 'boolean'],
            'student.disability_type_ids' => ['nullable', 'array'],
            'student.disability_type_ids.*' => ['integer'],
            'student.disability_details' => ['nullable', 'string'],

            'student.allergies' => ['nullable', 'string'],
            'student.emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'student.emergency_contact_phone' => ['nullable', 'string', 'max:32'],
        ];
    }
}

