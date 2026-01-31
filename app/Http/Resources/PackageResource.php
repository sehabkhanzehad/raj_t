<?php

namespace App\Http\Resources;

use App\Http\Resources\Api\GroupLeaderResource;
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
        $pilgrims = $this->isUmrah() ? $this->umrahs : $this->registrations;
        $registeredStatus = $this->isUmrah() ? 'registered' : 'active';

        $statistics = ['total_pilgrims' => $pilgrims->count()];

        // Add detailed statistics only for Umrah packages
        if ($this->isUmrah()) {
            $statistics['registered'] = $pilgrims->where('status', $registeredStatus)->count();
            $statistics['cancelled'] = $pilgrims->where('status', 'cancelled')->count();
            $statistics['completed'] = $pilgrims->where('status', 'completed')->count();
        }

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
                'statistics' => $statistics,
            ],
            'relationships' => [
                'groupLeaders' => GroupLeaderResource::collection($this->whenLoaded('groupLeaders')),
                'year' => new YearResource($this->whenLoaded('year')),
            ],
        ];
    }
}
