<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Country;         // Country model
use App\Models\City;            // City model
use App\Models\State;           // State model
use App\Models\AirLine;         // AirLine model
use App\Models\TransactionType; // TransactionType model
use App\Models\Supplier;

class InvoiceMainPaxPaymentResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id'             => $this->id,
			'invoice_number' => $this->invoice_number,
			'customer_id'    => $this->customer_id,
			// Only customer full name
			'customer' => $this->whenLoaded('customer', function () {
				return [
					'full_name' => $this->customer->full_name,
				];
			}),
			'airLine_id' => $this->airLine_id,
			// Only airline name
			'airLine' => $this->whenLoaded('airLine', function () {
				return [
					'airline_name' => $this->airLine->airline_name,
				];
			}),
			'travel_from' => $this->travel_from,
			'travel_to'   => $this->travel_to,
			// Only selected fromAirport fields
			'fromAirport' => $this->whenLoaded('fromAirport', function () {
				return [
					'icao' => $this->fromAirport->icao,
					'iata' => $this->fromAirport->iata,
					'city' => $this->fromAirport->city,
					'state' => $this->fromAirport->state,
				];
			}),
			// Only selected toAirport fields
			'toAirport' => $this->whenLoaded('toAirport', function () {
				return [
					'icao' => $this->toAirport->icao,
					'iata' => $this->toAirport->iata,
					'city' => $this->toAirport->city,
					'state' => $this->toAirport->state,
				];
			}),
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
