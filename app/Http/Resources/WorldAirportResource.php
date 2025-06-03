<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorldAirportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icao' => $this->icao,
            'iata' => $this->iata ?? '',
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state ?? '',
            'country' => $this->country,
            'elevation' => $this->elevation ?? null,
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'timezone' => $this->tz,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
