<?php

namespace App\Http\Requests\Company\Schools\Workshops;

use App\Models\School;
use App\Models\SchoolWorkshop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajuste para Policy/Gate se tiver
    }

    public function rules(): array
    {
        return [
            'workshop_id' => ['sometimes', 'integer', Rule::exists('workshops', 'id')],
            'starts_at'   => ['sometimes', 'date'],
            'ends_at'     => ['nullable', 'date'],
            'status'      => ['sometimes', Rule::in([
                SchoolWorkshop::STATUS_ACTIVE,
                SchoolWorkshop::STATUS_INACTIVE,
                SchoolWorkshop::STATUS_EXPIRED,
            ])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var School $school */
            $school = $this->route('school');
            /** @var SchoolWorkshop $current */
            $current = $this->route('schoolWorkshop');

            if (!$school || !$current) {
                return;
            }

            $workshopId = (int) ($this->input('workshop_id') ?? $current->workshop_id);
            $startsAt = $this->input('starts_at')
                ? $this->date('starts_at')->toDateString()
                : optional($current->starts_at)->toDateString();

            $endsAt = $this->input('ends_at') !== null
                ? ($this->input('ends_at') ? $this->date('ends_at')->toDateString() : null)
                : (optional($current->ends_at)->toDateString());

            if (!$workshopId || !$startsAt) {
                return;
            }

            // Se ends_at veio preenchido, respeita after starts_at
            if ($endsAt && $endsAt <= $startsAt) {
                $validator->errors()->add('ends_at', 'A data final deve ser posterior à data inicial.');
                return;
            }

            $q = SchoolWorkshop::query()
                ->where('school_id', $school->id)
                ->where('workshop_id', $workshopId)
                ->whereKeyNot($current->id);

            $q->where(function ($q) use ($startsAt) {
                $q->whereNull('ends_at')
                  ->orWhereDate('ends_at', '>', $startsAt);
            });

            if ($endsAt) {
                $q->whereDate('starts_at', '<', $endsAt);
            }

            if ($q->exists()) {
                $validator->errors()->add(
                    'starts_at',
                    'Esta alteração gera sobreposição de vigência com outro vínculo já existente.'
                );
            }
        });
    }
}

