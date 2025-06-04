<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardFormResource extends JsonResource
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
            'airLine_id' => (int) $this->airLine_id,
            'airLine' => new AirLineResource($this->whenLoaded('airLine')),
            'holder_name' => $this->holder_name,
            'type' => $this->type,
            'card_number' => $this->card_number,
            'expire_date' => $this->expire_date,
            'cvv' => $this->cvv,
            'amount' => $this->amount,
            'travel_date' => $this->travel_date,
            'transportation' => $this->transportation,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'phone_number' => $this->phone_number,
            'signature' => $this->signature,
            'full_path' => $this->full_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
