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
			//'customer'       => new CustomerResource($this->whenLoaded('customer')),
			'airLine_id' => $this->airLine_id,
			// Only airline name
			//'airLine' => new AirLineResource($this->whenLoaded('airLine')),
			'travel_from' => $this->travel_from,
			'travel_to'   => $this->travel_to,
		
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
