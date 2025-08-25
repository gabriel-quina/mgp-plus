<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'cep'  => $this->cep,
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id'    => $this->city->id,
                    'name'  => $this->city->name,
                    'state' => [
                        'id'   => $this->city->state->id ?? null,
                        'name' => $this->city->state->name ?? null,
                        'uf'   => $this->city->state->uf ?? null,
                    ],
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

