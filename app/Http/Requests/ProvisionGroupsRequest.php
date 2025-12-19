<?php

namespace App\Http\Requests;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProvisionGroupsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workshop_id' => ['required', 'integer', 'exists:workshops,id'],
            'grade_level_ids' => ['required', 'array', 'min:1'],
            'grade_level_ids.*' => ['integer', 'exists:grade_levels,id'],
            'academic_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'shift' => ['required', Rule::in(['morning', 'afternoon', 'evening'])],
            'max_students' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $school = $this->route('school');
            $schoolId = $school instanceof School ? $school->id : (int) $school;
            $workshopId = (int) $this->input('workshop_id');

            if (! $schoolId || ! $workshopId) {
                return;
            }

            $exists = \App\Models\School::query()
                ->whereKey($schoolId)
                ->whereHas('workshops', fn ($q) => $q->whereKey($workshopId))
                ->exists();

            if (! $exists) {
                $v->errors()->add('workshop_id', 'A oficina selecionada não pertence a esta escola.');
            }
        });
    }
}
