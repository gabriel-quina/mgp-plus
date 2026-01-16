<?php

namespace App\Http\Requests\Schools;

use Illuminate\Foundation\Http\FormRequest;

class SchoolStudentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'grade_level' => ['nullable', 'integer', 'exists:grade_levels,id'],
            'cols' => ['nullable', 'array'],
            'cols.*' => ['string', 'in:avg,att'],
        ];
    }
}
