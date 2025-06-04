<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CruiseDetailResource extends JsonResource
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
            'customer_invoice_id' => $this->customer_invoice_id,
            'package_name' => $this->package_name,
            'country_id' => $this->country_id,
            'countryName' => new CountryResource($this->whenLoaded('countryName')), 
            'start_date' => $this->start_date,
            //'users' => UserResource::collection($this->whenLoaded('users')),
            'end_date' => $this->end_date,
            'operator' => $this->operator,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
