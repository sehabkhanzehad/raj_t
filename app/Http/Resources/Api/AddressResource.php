<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'address',
            'id' => $this->id,
            'attributes' => [
                'house_no' => $this->house_no,
                'road_no' => $this->road_no,
                'village' => $this->village,
                'post_office' => $this->post_office,
                'police_station' => $this->police_station,
                'district' => $this->district,
                'division' => $this->division,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'type' => $this->type,
                'formattedAddress' => $this->formatted_address,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
