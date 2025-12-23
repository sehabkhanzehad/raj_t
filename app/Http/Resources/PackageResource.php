<?php

namespace App\Http\Resources;

use App\Http\Resources\Api\YearResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'package',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'type' => $this->type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'duration_days' => $this->duration_days,
                'price' => $this->price,
                'description' => $this->description,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'year' => new YearResource($this->whenLoaded('year')),
            ],
        ];
    }
}
