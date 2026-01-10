<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\PackageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UmrahResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'umrah',
            'id' => $this->id,
            'attributes' => [
                'status' => $this->status,
                'discount' => $this->discount,
                'totalCollect' => $this->totalCollect(),
                'totalRefund' => $this->totalRefund(),
                'totalPaid' => $this->totalPaid(),
                'dueAmount' => $this->dueAmount(),
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'year' => new YearResource($this->whenLoaded('year')),
                'groupLeader' => new GroupLeaderResource($this->whenLoaded('groupLeader')),
                'pilgrim' => new PilgrimResource($this->whenLoaded('pilgrim')),
                'package' => new PackageResource($this->whenLoaded('package')),
                'passport' => $this->when($this->resource->relationLoaded('passports'), fn() => $this->passports->first() ? new PassportResource($this->passports->first()) : null),
            ],
        ];
    }
}
