<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'school_id' => ['required','exists:schools,id'],
            'name'      => ['required','string','max:120'],
            'cpf'       => ['nullable','string','max:20','unique:students,cpf'],
            'email'     => ['nullable','email','max:255','unique:students,email'],
            'birthdate' => ['nullable','date'],
        ];
    }
}

