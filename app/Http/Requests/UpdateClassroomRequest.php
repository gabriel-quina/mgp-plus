<?php

namespace App\Http\Requests;

use App\Models\{
    Classroom
};
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
        $year = $this->input('academic_year');
        $this->merge([
            'academic_year'   => $year !== null && $year !== '' ? (int) $year : (int) date('Y'),
            'is_active'       => $this->boolean('is_active'),
            'grade_level_key' => $this->makeGradeLevelKey((array) $this->input('grade_level_ids', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            'school_id'            => ['required', 'integer', 'exists:schools,id'],
            'parent_classroom_id'  => ['nullable', 'integer', 'exists:classrooms,id'],
            'name'                 => ['required', 'string', 'max:150'],
            'shift'                => ['required', Rule::in(['morning', 'afternoon', 'evening'])],
            'is_active'            => ['sometimes', 'boolean'],

            'academic_year'        => ['required', 'integer', 'min:2000', 'max:2100'],

            'grade_level_ids'      => ['required', 'array', 'min:1'],
            'grade_level_ids.*'    => ['integer', 'exists:grade_levels,id'],

            'grade_level_key'      => ['required', 'string', 'max:191'],

            'workshops'                => ['nullable', 'array'],
            'workshops.*.id'           => ['nullable', 'integer', 'exists:workshops,id'],
            'workshops.*.max_students' => ['nullable', 'integer', 'min:0'],
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
                ->where('academic_year', $this->input('academic_year'))
                ->where('shift', $this->input('shift'))
                ->where('grade_level_key', $this->input('grade_level_key'))
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
            'parent_classroom_id'=> 'Turma Pai',
            'name'              => 'Nome',
            'shift'             => 'Turno',
            'is_active'         => 'Ativa',
            'academic_year'     => 'Ano letivo',
            'grade_level_ids'   => 'Anos atendidos',
            'grade_level_key'   => 'Conjunto de anos',
            'workshops.*.id'    => 'Oficina',
            'workshops.*.max_students' => 'Capacidade',
        ];
    }

    private function makeGradeLevelKey(array $ids): string
    {
        return collect($ids)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->sort()
            ->values()
            ->implode(',');
    }
}

