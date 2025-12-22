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
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'motherName' => $this->mother_name,
                'fatherName' => $this->father_name,
                'phone' => $this->phone,
                'nid' => $this->nid,
                'dateOfBirth' => $this->date_of_birth,
                'address' => $this->address,
                'gender' => $this->gender,
                'notes' => $this->notes,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
