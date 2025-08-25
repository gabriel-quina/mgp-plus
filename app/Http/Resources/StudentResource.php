<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'cpf'       => $this->cpf,
            'email'     => $this->email,
            'birthdate' => $this->birthdate?->toDateString(),
            'school'    => $this->whenLoaded('school', function () {
                return [
                    'id'   => $this->school->id,
                    'name' => $this->school->name,
                ];
            }),
            'created_at'=> $this->created_at?->toISOString(),
            'updated_at'=> $this->updated_at?->toISOString(),
        ];
    }
}

