<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'loan',
            'id' => $this->id,
            'attributes' => [
                'amount' => $this->amount,
                'direction' => $this->direction,
                'date' => $this->date ? $this->date->format('Y-m-d') : null,
                'description' => $this->description,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'loanable' => new UserResource($this->whenLoaded('loanable')),
            ],
        ];
    }
}
