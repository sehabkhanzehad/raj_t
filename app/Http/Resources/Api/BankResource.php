<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "type" => "Bank",
            "id" => $this->id,
            "attributes" => [
                "name" => $this->name,
                "branch" => $this->branch,
                "accountNumber" => $this->account_number,
                "accountHolderName" => $this->account_holder_name,
                "address" => $this->address,
                "accountType" => $this->account_type,
                "routingNumber" => $this->routing_number,
                "swiftCode" => $this->swift_code,
                "openingDate" => $this->opening_date,
                "phone" => $this->phone,
                "telephone" => $this->telephone,
                "email" => $this->email,
                "website" => $this->website,
                "status" => $this->status,
                "createdAt" => $this->created_at,
                "updatedAt" => $this->updated_at,
            ],
            'relationships' => [
                'section' => new SectionResource($this->whenLoaded('section')),
            ],
        ];
    }
}
