<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherEngagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'engagement_type' => $this->filled('engagement_type')
                ? strtolower(trim($this->input('engagement_type')))
                : null,
            'status' => $this->filled('status')
                ? strtolower(trim($this->input('status')))
                : 'active',
            'notes' => $this->filled('notes') ? trim($this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'engagement_type' => ['required', Rule::in(['our_clt', 'our_pj', 'our_temporary', 'municipal'])],
            'hours_per_week'  => ['required', 'integer', 'min:1', 'max:44'],
            'status'          => ['required', Rule::in(['active', 'suspended', 'ended'])],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            // city_id é obrigatório somente quando municipal; proibido nos demais
            'city_id'         => ['nullable', 'integer', 'exists:cities,id'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type = $this->input('engagement_type');
            $city = $this->input('city_id');

            if ($type === 'municipal' && empty($city)) {
                $v->errors()->add('city_id', 'Selecione a cidade para vínculo municipal.');
            }

            if (in_array($type, ['our_clt', 'our_pj', 'our_temporary'], true) && !empty($city)) {
                $v->errors()->add('city_id', 'Cidade não deve ser informada para vínculos da nossa empresa.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'engagement_type' => 'tipo de vínculo',
            'hours_per_week'  => 'horas por semana',
            'status'          => 'status',
            'start_date'      => 'data de início',
            'end_date'        => 'data de término',
            'city_id'         => 'cidade',
            'notes'           => 'observações',
        ];
    }
}

