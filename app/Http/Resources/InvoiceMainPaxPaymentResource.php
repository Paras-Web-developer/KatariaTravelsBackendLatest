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
		// ---------------------------
		// Build and return the resource array.
		// ---------------------------
		return [
			'id'                         => $this->id,
			'invoice_number'             => $this->invoice_number,
			'customer_id'                => $this->customer_id,
			'customer'                   => new CustomerResource($this->whenLoaded('customer')),
			'airLine_id'                 => $this->airLine_id,
			'airLine'                    => new AirLineResource($this->whenLoaded('airLine')),
			'created_at'                 => $this->created_at,
			'updated_at'                 => $this->updated_at,
		];
	}
}
