<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['required','string','max:150'],
            'city_id' => ['required','exists:cities,id'],
            'cep'     => ['nullable','string','size:8'],
        ];
    }
}

