<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInvoiceResource extends JsonResource
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
            'airLine' => new AirLineResource($this->whenLoaded('airLine')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'invoice_number' => $this->invoice_number,
            'booking_date' => $this->booking_date,
            'departure_date' => $this->departure_date,
            'sales_agent' => $this->sales_agent,
            'travel_from' => $this->travel_from,
            'travel_to' => $this->travel_to,
            'gds_locator' => $this->gds_locator,
            'gds_type' => $this->gds_type,
            'itinerary' => $this->itinerary,
            'flightDetails' => FlightDetailResource::collection($this->whenLoaded('flightDetails')),
            'passengerDetails' => PassengerDetailResource::collection($this->whenLoaded('passengerDetails')),
            'paymentDetails' =>  PaymentDetailResource::collection($this->whenLoaded('paymentDetails')),
            'insuranceDetails' => InsuranceDetailResource::collection($this->whenLoaded('insuranceDetails')),
            'hotelCruiseLandPackages' => HotelCruiseLandPackageResource::collection($this->whenLoaded('hotelCruiseLandPackages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
