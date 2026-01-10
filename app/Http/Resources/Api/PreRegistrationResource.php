<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "type" => "pre-registration",
            "id" => $this->id,
            "attributes" => [
                "serialNo" => $this->serial_no,
                "trackingNo" => $this->tracking_no,
                "bankVoucherNo" => $this->bank_voucher_no,
                "voucherName" => $this->voucher_name,
                "date" => $this->date,
                "status" => $this->status,
                "archiveDate" => $this->archive_date,
                "createdAt" => $this->created_at,
                "updatedAt" => $this->updated_at,
            ],
            "relationships" => [
                "pilgrim" => new PilgrimResource($this->whenLoaded("pilgrim")),
                "groupLeader" => new GroupLeaderResource($this->whenLoaded("groupLeader")),
                "bank" => new BankResource($this->whenLoaded("bank")),
                'passport' => $this->when($this->resource->relationLoaded('passports'), fn() => $this->passport() ? new PassportResource($this->passport()) : null),
            ],
        ];
    }
}
