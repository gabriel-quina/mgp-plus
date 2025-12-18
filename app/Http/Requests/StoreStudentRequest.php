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
        $ts = (string) $this->input('enrollment.transfer_scope', 'first');
        $originId = (string) $this->input('enrollment.origin_school_id', '');

        $needsOriginName = fn () => in_array($ts, ['internal', 'external'], true) && $originId === '';
        $needsExternalCityState = fn () => $ts === 'external' && $originId === '';

        return [
            // STUDENT
            'student.name' => ['required', 'string', 'max:120'],
            'student.social_name' => ['nullable', 'string', 'max:120'],

            // CPF/email NÃO têm unique aqui: a regra real é “matrícula ativa” (controller) + conflito de email (controller)
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

            // ENROLLMENT
            'enrollment.destination_school_id' => ['required', 'integer', 'exists:schools,id'],
            'enrollment.academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'enrollment.grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'enrollment.shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'enrollment.started_at' => ['nullable', 'date'],
            'enrollment.transfer_scope' => ['required', Rule::in(['first', 'internal', 'external'])],

            // ORIGEM (ignora tudo se first)
            'enrollment.origin_school_id' => ['exclude_if:enrollment.transfer_scope,first', 'nullable', 'integer', 'exists:schools,id'],

            // Se não selecionou escola por ID, precisa informar o nome (para criar histórica se necessário)
            'enrollment.origin_school_name' => [
                'exclude_if:enrollment.transfer_scope,first',
                'nullable',
                'string',
                'max:150',
                Rule::requiredIf($needsOriginName),
            ],

            // Externa sem ID: precisa cidade/estado para criar a escola histórica corretamente
            'enrollment.origin_city_name' => [
                'exclude_if:enrollment.transfer_scope,first',
                'nullable',
                'string',
                'max:120',
                Rule::requiredIf($needsExternalCityState),
            ],
            'enrollment.origin_state_id' => [
                'exclude_if:enrollment.transfer_scope,first',
                'nullable',
                'integer',
                'exists:states,id',
                Rule::requiredIf($needsExternalCityState),
            ],
        ];
    }
}

