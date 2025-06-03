<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassengerDetailResource extends JsonResource
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
            'first_name' => $this->first_name,
            //'airLine' =>  new AirLineResource($this->whenLoaded('airLine')),
            'last_name' => $this->last_name,
            'title' => $this->title,
            'bill_to' => $this->bill_to,
            'add_insurance' => $this->add_insurance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
