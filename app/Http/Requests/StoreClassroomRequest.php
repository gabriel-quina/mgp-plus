<?php

namespace App\Http\Requests;

use App\Models\{
    Classroom
};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste regras de auth se necessário
        return true;
    }

    /**
     * Normaliza entrada antes da validação:
     * - academic_year_id padrão = ano corrente se vazio
     * - grades_signature = string canônica "1,2,3"
     */
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
            'school_id'        => $schoolId,
            'academic_year_id' => $year !== null && $year !== '' ? (int) $year : (int) date('Y'),
            'grades_signature' => $this->makeGradesSignature((array) $this->input('grade_level_ids', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            // Turma
            'school_id'            => ['required', 'integer', 'exists:schools,id'],
            'shift'                => ['required', Rule::in(['morning', 'afternoon', 'evening'])],
            'workshop_id'          => ['required', 'integer', 'exists:workshops,id'],
            'group_number'         => ['required', 'integer', 'min:1'],
            'capacity_hint'        => ['nullable', 'integer', 'min:1'],
            'status'               => ['required', 'string', 'max:50'],

            // Ano letivo (anual)
            'academic_year_id'     => ['required', 'integer', 'min:2000', 'max:2100'],

            // Anos atendidos (obrigatório ≥ 1)
            'grade_level_ids'      => ['required', 'array', 'min:1'],
            'grade_level_ids.*'    => ['integer', 'exists:grade_levels,id'],

            // Chave canônica do conjunto (gerada no prepareForValidation)
            'grades_signature'     => ['required', 'string', 'max:191'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Regra de unicidade: 1 turma por (escola, ano, turno, conjunto)
            $exists = Classroom::query()
                ->where('school_id', $this->input('school_id'))
                ->where('academic_year_id', $this->input('academic_year_id'))
                ->where('shift', $this->input('shift'))
                ->where('workshop_id', $this->input('workshop_id'))
                ->where('grades_signature', $this->input('grades_signature'))
                ->where('group_number', $this->input('group_number'))
                ->exists();

            if ($exists) {
                $v->errors()->add(
                    'grade_level_ids',
                    'Já existe uma turma para este conjunto de anos, turno e ano letivo nesta escola.'
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

    /**
     * Constrói a string canônica "1,2,3" para o conjunto de anos.
     * - remove duplicatas
     * - ordena crescente
     */
    private function makeGradesSignature(array $ids): string
    {
        return collect($ids)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->sort()
            ->values()
            ->implode(',');
    }
}
