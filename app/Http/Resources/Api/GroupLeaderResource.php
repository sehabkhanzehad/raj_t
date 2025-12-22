<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupLeaderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'group-leader',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'section' => new SectionResource($this->whenLoaded('section')),
                'pilgrim' => new PilgrimResource($this->whenLoaded('pilgrim')),
            ],
        ];
    }
}
