<?php

namespace App\Http\Requests\Schools\Students;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ======================
            // STUDENT
            // ======================
            'student.name' => ['required', 'string', 'max:120'],

            // Sem unique aqui (você já tratava conflito no controller/modelagem)
            'student.cpf' => ['nullable', 'string', 'max:20'],
            'student.email' => ['nullable', 'email', 'max:255'],

            'student.birthdate' => ['nullable', 'date'],
            'student.race_color' => ['nullable', 'string', 'max:20'],

            'student.has_disability' => ['nullable', 'boolean'],
            'student.disability_type_ids' => ['nullable', 'array'],
            'student.disability_type_ids.*' => ['integer'],
            'student.disability_details' => ['nullable', 'string'],

            'student.allergies' => ['nullable', 'string'],
            'student.emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'student.emergency_contact_phone' => ['nullable', 'string', 'max:32'],

            // ======================
            // ENROLLMENT (somente campos do form)
            // ======================
            'enrollment.grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'enrollment.academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'enrollment.shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'enrollment.started_at' => ['nullable', 'date'],
        ];
    }
}

