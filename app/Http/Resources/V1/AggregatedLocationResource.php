<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AggregatedLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'day' => $this->day, // From SQL alias
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'timestamp' => (int) $this->timestamp_noon_ms // From SQL alias
        ];
    }
}
