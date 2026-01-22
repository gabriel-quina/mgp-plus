<?php

namespace App\Http\Requests\Company\Schools\Workshops;

use App\Models\School;
use App\Models\SchoolWorkshop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajuste para Policy/Gate se tiver
    }

    public function rules(): array
    {
        return [
            'workshop_id' => ['required', 'integer', Rule::exists('workshops', 'id')],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after:starts_at'],
            'status'      => ['required', Rule::in([
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

            $workshopId = (int) $this->input('workshop_id');
            $startsAt = $this->date('starts_at')?->toDateString();
            $endsAt = $this->input('ends_at') ? $this->date('ends_at')->toDateString() : null;

            if (!$school || !$workshopId || !$startsAt) {
                return;
            }

            $q = SchoolWorkshop::query()
                ->where('school_id', $school->id)
                ->where('workshop_id', $workshopId);

            // Regra de sobreposição (intervalos [start, end) com end exclusivo):
            // overlap se existing_end > new_start (ou existing_end null) E existing_start < new_end (se new_end não for null)
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
                    'Já existe um vínculo para esta oficina com vigência que sobrepõe o período informado.'
                );
            }
        });
    }
}

