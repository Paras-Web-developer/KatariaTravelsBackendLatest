<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'title' => $this->title,
            'fax' => $this->fax,
            'company_name' => $this->company_name,

            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'alternate_phone' => $this->alternate_phone,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'languages' => $this->languages,
            'medical_information' => $this->medical_information,
            'passport_number' => $this->passport_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
