<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('student')->id ?? null;

        return [
            'school_id' => ['sometimes','exists:schools,id'],
            'name'      => ['sometimes','string','max:120'],
            'cpf'       => ['nullable','string','max:20',"unique:students,cpf,{$id}"],
            'email'     => ['nullable','email','max:255',"unique:students,email,{$id}"],
            'birthdate' => ['nullable','date'],
        ];
    }
}

