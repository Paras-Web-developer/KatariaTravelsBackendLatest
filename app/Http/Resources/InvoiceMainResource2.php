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

class InvoiceMainResource2 extends JsonResource
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
			'invoice_id'   => $this->invoice_id,
			'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
			'invoice_number'             => $this->invoice_number,
			'itinerary'                  => $this->itinerary,
			'mco'                        => $this->mco,
			'status'                     => $this->status,
			'created_at'                 => $this->created_at,
			'updated_at'                 => $this->updated_at,
		];
	}
}
