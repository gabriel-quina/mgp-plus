<?php

namespace App\Http\Requests;

use App\Models\School;
use App\Models\Teacher;
use App\Models\TeacherCityAccess;
use App\Models\TeacherEngagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'academic_year'  => $this->input('academic_year') ? (int) $this->input('academic_year') : null,
            'hours_per_week' => $this->input('hours_per_week') !== null ? (int) $this->input('hours_per_week') : null,
            'shift'          => $this->filled('shift') ? strtolower(trim($this->input('shift'))) : null,
            'notes'          => $this->filled('notes') ? trim($this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var \App\Models\TeachingAssignment $assignment */
        $assignment = $this->route('teaching_assignment');

        $teacher   = $this->route('teacher');
        $teacherId = is_object($teacher) ? $teacher->id : (int) $teacher;

        $schoolId  = (int) $this->input('school_id');
        $year      = (int) $this->input('academic_year');
        $shift     = $this->input('shift'); // pode ser null

        return [
            'school_id'      => [
                'required',
                'integer',
                'exists:schools,id',
                // unicidade composta, ignorando o próprio registro
                \Illuminate\Validation\Rule::unique('teaching_assignments')->where(function ($q) use ($teacherId, $schoolId, $year, $shift) {
                    $q->where('teacher_id', $teacherId)
                        ->where('school_id', $schoolId)
                        ->where('academic_year', $year);

                    if (is_null($shift)) {
                        $q->whereNull('shift');
                    } else {
                        $q->where('shift', $shift);
                    }
                })->ignore($assignment->id),
            ],
            'engagement_id'  => ['nullable', 'integer', 'exists:teacher_engagements,id'],
            'academic_year'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'shift'          => ['nullable', \Illuminate\Validation\Rule::in(['morning', 'afternoon', 'evening'])],
            'hours_per_week' => ['nullable', 'integer', 'min:1', 'max:44'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            /** @var Teacher $teacher */
            $teacher = $this->route('teacher');
            $teacherId = is_object($teacher) ? $teacher->id : (int) $teacher;

            $school = School::with('city')->find($this->input('school_id'));
            if (!$school) return;

            $engagementId = $this->input('engagement_id');

            if ($engagementId) {
                $eng = TeacherEngagement::find($engagementId);
                if (!$eng || (int)$eng->teacher_id !== (int)$teacherId) {
                    $v->errors()->add('engagement_id', 'Vínculo inválido para este professor.');
                    return;
                }

                if ($eng->engagement_type === 'municipal') {
                    if ((int)$eng->city_id !== (int)$school->city_id) {
                        $v->errors()->add('school_id', 'Para vínculo municipal, a escola deve ser da mesma cidade do vínculo.');
                    }
                } else {
                    $hasAccess = TeacherCityAccess::where('teacher_id', $teacherId)
                        ->where('city_id', $school->city_id)->exists();
                    if (!$hasAccess) {
                        $v->errors()->add('school_id', 'Professor não possui acesso à cidade desta escola para este tipo de vínculo.');
                    }
                }
            } else {
                $hasAccess = TeacherCityAccess::where('teacher_id', $teacherId)
                    ->where('city_id', $school->city_id)->exists();

                $hasMunicipal = TeacherEngagement::where('teacher_id', $teacherId)
                    ->where('engagement_type', 'municipal')
                    ->where('city_id', $school->city_id)
                    ->where('status', 'active')
                    ->exists();

                if (!$hasAccess && !$hasMunicipal) {
                    $v->errors()->add('school_id', 'Professor não tem acesso a esta cidade e não possui vínculo municipal ativo nela.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'school_id'      => 'escola',
            'engagement_id'  => 'vínculo',
            'academic_year'  => 'ano letivo',
            'shift'          => 'turno',
            'hours_per_week' => 'horas por semana',
            'notes'          => 'observações',
        ];
    }
}
