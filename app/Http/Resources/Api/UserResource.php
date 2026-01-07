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
                'fullName' => $this->full_name,
                'firstNameBangla' => $this->first_name_bangla,
                'lastNameBangla' => $this->last_name_bangla,
                'fullNameBangla' => $this->full_name_bangla,
                'motherName' => $this->mother_name,
                'motherNameBangla' => $this->mother_name_bangla,
                'fatherName' => $this->father_name,
                'fatherNameBangla' => $this->father_name_bangla,
                'occupation' => $this->occupation,
                'spouseName' => $this->spouse_name,
                'avatar' => $this->avatar ?? null,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'isMarried' => $this->is_married,
                'nid' => $this->nid,
                'birthCertificateNumber' => $this->birth_certificate_number,
                'dateOfBirth' => $this->date_of_birth,
                'address' => $this->address,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'presentAddress' => new AddressResource($this->whenLoaded('presentAddress')),
                'permanentAddress' => new AddressResource($this->whenLoaded('permanentAddress')),
            ],
        ];
    }
}
