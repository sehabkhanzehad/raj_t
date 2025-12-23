<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PilgrimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'pilgrim',
            'id' => $this->id,
            'attributes' => [
                'notes' => $this->notes,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'user' => new UserResource($this->whenLoaded('user')),
            ],
        ];
    }
}
