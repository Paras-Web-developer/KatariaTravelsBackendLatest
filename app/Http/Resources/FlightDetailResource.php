<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightDetailResource extends JsonResource
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
            'airLine_id' => $this->airLine_id,
            'airLine' =>  new AirLineResource($this->whenLoaded('airLine')),
            'from' => $this->from,
            'to' => $this->to,
            'date' => $this->date,
            'flight_no' => $this->flight_no,
            'dep_time' => $this->dep_time,
            'arr_time' => $this->arr_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
