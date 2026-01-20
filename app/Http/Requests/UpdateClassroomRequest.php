<?php

namespace App\Http\Requests;

use App\Models\{
    Classroom,
    SchoolWorkshop
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
            'school_workshop_id'   => ['required', 'integer', 'exists:school_workshop,id'],
            'name'                 => ['required', 'string', 'max:150'],
            'shift'                => ['required', Rule::in(['morning', 'afternoon', 'evening'])],
            'is_active'            => ['sometimes', 'boolean'],

            'academic_year'        => ['required', 'integer', 'min:2000', 'max:2100'],

            'grade_level_ids'      => ['required', 'array', 'min:1'],
            'grade_level_ids.*'    => ['integer', 'exists:grade_levels,id'],

            'grade_level_key'      => ['required', 'string', 'max:191'],
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

            $schoolWorkshopId = (int) $this->input('school_workshop_id');
            if (! $schoolWorkshopId) {
                return;
            }

            $schoolWorkshop = SchoolWorkshop::query()->find($schoolWorkshopId);
            if (! $schoolWorkshop) {
                return;
            }

            if ($schoolWorkshop->school_id !== (int) $this->input('school_id')) {
                $v->errors()->add('school_workshop_id', 'O contrato de oficina não pertence à escola informada.');
                return;
            }

            $currentClassroom = $this->route('classroom') instanceof Classroom
                ? $this->route('classroom')
                : Classroom::query()->find($classroomId);

            $currentSchoolWorkshopId = $currentClassroom?->school_workshop_id;

            if ((int) $currentSchoolWorkshopId !== $schoolWorkshopId && ! $schoolWorkshop->isActiveOn()) {
                $v->errors()->add('school_workshop_id', 'O contrato de oficina informado não está ativo.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'school_id'         => 'Escola',
            'parent_classroom_id'=> 'Turma Pai',
            'school_workshop_id' => 'Contrato de oficina',
            'name'              => 'Nome',
            'shift'             => 'Turno',
            'is_active'         => 'Ativa',
            'academic_year'     => 'Ano letivo',
            'grade_level_ids'   => 'Anos atendidos',
            'grade_level_key'   => 'Conjunto de anos',
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
