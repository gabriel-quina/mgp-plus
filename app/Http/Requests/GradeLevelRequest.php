<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'sequence'   => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }
}

