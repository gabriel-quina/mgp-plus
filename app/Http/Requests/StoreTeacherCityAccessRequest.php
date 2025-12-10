<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherCityAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'city_id' => $this->input('city_id') ? (int) $this->input('city_id') : null,
        ]);
    }

    public function rules(): array
    {
        // O parâmetro de rota é {teacher} (model bound)
        $teacher = $this->route('teacher');

        return [
            'city_id' => [
                'required',
                'integer',
                'exists:cities,id',
                // garante unicidade do par teacher-city
                Rule::unique('teacher_city_access', 'city_id')->where(fn ($q) =>
                    $q->where('teacher_id', is_object($teacher) ? $teacher->id : $teacher)
                ),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'city_id' => 'cidade',
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.unique' => 'Este professor já possui acesso a esta cidade.',
        ];
    }
}

