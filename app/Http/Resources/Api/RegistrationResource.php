<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\PreRegistrationResource;
use App\Http\Resources\PackageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "type" => "registration",
            "id" => $this->id,
            "attributes" => [
                "date" => $this->date,
                "status" => $this->status,
                "createdAt" => $this->created_at,
                "updatedAt" => $this->updated_at,
            ],
            "relationships" => [
                "preRegistration" => new PreRegistrationResource($this->whenLoaded("preRegistration")),
                "pilgrim" => new PilgrimResource($this->whenLoaded("pilgrim")),
                "bank" => new BankResource($this->whenLoaded("bank")),
                "package" => new PackageResource($this->whenLoaded("package")),
            ],
        ];
    }
}
