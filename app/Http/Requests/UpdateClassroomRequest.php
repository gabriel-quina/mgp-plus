<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $schoolFromRoute = $this->route('school');
        $schoolId = $this->input('school_id');

        if ($schoolFromRoute instanceof \App\Models\School) {
            $schoolId = $schoolFromRoute->id;
        } elseif (is_numeric($schoolFromRoute)) {
            $schoolId = (int) $schoolFromRoute;
        }

        $year = $this->input('academic_year_id');
        $this->merge([
            'school_id' => $schoolId,
            'academic_year_id' => $year !== null && $year !== '' ? (int) $year : (int) date('Y'),
            'grades_signature' => Classroom::buildGradesSignature((array) $this->input('grade_level_ids', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            'school_id'            => ['required', 'integer', 'exists:schools,id'],
            'shift'                => ['required', Rule::in(['morning', 'afternoon', 'evening'])],
            'workshop_id'          => ['required', 'integer', 'exists:workshops,id'],
            'group_number'         => ['required', 'integer', 'min:1'],
            'capacity_hint'        => ['nullable', 'integer', 'min:1'],
            'status'               => ['required', 'string', 'max:50'],

            'academic_year_id'     => ['required', 'integer', 'min:2000', 'max:2100'],

            'grade_level_ids'      => ['required', 'array', 'min:1'],
            'grade_level_ids.*'    => ['integer', 'exists:grade_levels,id'],

            'grades_signature'     => ['required', 'string', 'max:191'],
        ];
    }

    public function withValidator($validator)
    {
        $classroomId = $this->route('classroom') instanceof Classroom
            ? $this->route('classroom')->id
            : (int) $this->route('classroom');

        $validator->after(function ($v) use ($classroomId) {
            $conflict = Classroom::query()
                ->where('school_id', $this->input('school_id'))
                ->where('academic_year_id', $this->input('academic_year_id'))
                ->where('shift', $this->input('shift'))
                ->where('workshop_id', $this->input('workshop_id'))
                ->where('grades_signature', $this->input('grades_signature'))
                ->where('group_number', $this->input('group_number'))
                ->where('id', '!=', $classroomId)
                ->exists();

            if ($conflict) {
                $v->errors()->add(
                    'grade_level_ids',
                    'Já existe outra turma para este conjunto de anos, turno e ano letivo nesta escola.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'school_id'         => 'Escola',
            'shift'             => 'Turno',
            'workshop_id'       => 'Oficina',
            'group_number'      => 'Grupo',
            'capacity_hint'     => 'Capacidade sugerida',
            'status'            => 'Status',
            'academic_year_id'  => 'Ano letivo',
            'grade_level_ids'   => 'Anos atendidos',
            'grades_signature'  => 'Conjunto de anos',
        ];
    }

}
