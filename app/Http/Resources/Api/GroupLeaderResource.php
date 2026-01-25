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
                'groupName' => $this->group_name,
                'trackPayment' => $this->pilgrim_required,
                'status' => $this->status,
                'preRegistrationsCount' => $this->active_pre_registrations_count,
                'registrationsCount' => $this->registrations_count,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'section' => new SectionResource($this->whenLoaded('section')),
                'user' => new UserResource($this->whenLoaded('user')),
            ],
        ];
    }
}
