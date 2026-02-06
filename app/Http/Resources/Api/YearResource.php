<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class YearResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Year',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'startDate' => $this->start_date,
                'endDate' => $this->end_date,
                'status' => $this->status,
                'default' => $this->default,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
