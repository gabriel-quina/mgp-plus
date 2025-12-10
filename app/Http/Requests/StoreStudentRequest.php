<?php

namespace App\Http\Requests;

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
            // STUDENT (vem de student[...])
            'student.name' => ['required', 'string', 'max:120'],
            'student.social_name' => ['nullable', 'string', 'max:120'],
            'student.cpf' => ['nullable', 'string', 'max:20', 'unique:students,cpf'],
            'student.email' => ['nullable', 'email', 'max:255', 'unique:students,email'],
            'student.birthdate' => ['nullable', 'date'],
            'student.race_color' => ['nullable', 'string', 'max:20'],
            'student.has_disability' => ['nullable', 'boolean'],
            'student.disability_type_ids' => ['nullable', 'array'],
            'student.disability_type_ids.*' => ['integer'],
            'student.disability_details' => ['nullable', 'string'],
            'student.allergies' => ['nullable', 'string'],
            'student.emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'student.emergency_contact_phone' => ['nullable', 'string', 'max:32'],

            // ENROLLMENT (vem de enrollment[...]) — matrícula inicial
            'enrollment.destination_school_id' => ['required', 'integer', 'exists:schools,id'],
            'enrollment.academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'enrollment.grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'enrollment.shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'enrollment.started_at' => ['nullable', 'date'],
            'enrollment.transfer_scope' => ['required', Rule::in(['first', 'internal', 'external'])],

            // ORIGEM (dependente do escopo)
            'enrollment.origin_school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'enrollment.origin_school_name' => ['nullable', 'string', 'max:150'],
            'enrollment.origin_city_name' => ['nullable', 'string', 'max:120'],
            'enrollment.origin_state_id' => ['nullable', 'integer', 'exists:states,id'],
        ];
    }
}
