<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'motherName' => $this->mother_name,
                'fatherName' => $this->father_name,
                'avatar' => $this->avatar ?? null,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'isMarried' => $this->is_married,
                'nid' => $this->nid,
                'dateOfBirth' => $this->date_of_birth,
                'address' => $this->address,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
