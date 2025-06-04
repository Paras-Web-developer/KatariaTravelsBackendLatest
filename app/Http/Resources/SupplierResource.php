<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'country_id' => $this->country_id,
            'countryName' => new CountryResource($this->whenLoaded('countryName')), 
            'state_id' => $this->state_id,
            'stateName' => new StateResource($this->whenLoaded('stateName')),  
            'city_id' => $this->city_id,
            'cityName' => new CityResource($this->whenLoaded('cityName')),  
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'supplier_code' => $this->supplier_code,
            'reservations_phone' => $this->reservations_phone,
            'reservations_email' => $this->reservations_email,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
