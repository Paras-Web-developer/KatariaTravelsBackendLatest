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
		$hotelFromPaxPendingPayment = 0;

		$airticketFromPaxPendingPayment = 0;

		$cruiseFromPaxPendingPayment = 0;

		$insuranceFromPaxPendingPayment = 0;

		$landPackageFromPaxPendingPayment = 0;

		$miscFromPaxPendingPayment = 0;

		$airticket = $this->airticket;
		if (is_string($airticket)) {
			$airticket = json_decode($airticket, true);
		}
		if ($airticket && isset($airticket['airticket_from_pax'])) {
			$totalAmount = $airticket['airticket_from_pax']['total'] ?? null;
			$totalPaidAmount = $airticket['airticket_from_pax']['amountPaid'] ?? null;
			$refund = $airticket['airticket_from_pax']['refund'] ?? null;
			$airticketFromPaxPendingPayment = $airticketFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}


		$hotel = $this->hotel;

		if (is_string($hotel)) {
			$hotel = json_decode($hotel, true);
		}
		if ($hotel && isset($hotel['hotel_from_pax'])) {

			$totalAmount = $hotel['hotel_from_pax']['total'] ?? null;

			$totalPaidAmount = $hotel['hotel_from_pax']['amountPaid'] ?? null;
			$refund = $hotel['hotel_from_pax']['refund'] ?? null;
			$hotelFromPaxPendingPayment = $hotelFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}

		$cruise = $this->cruise;
		if (is_string($cruise)) {
			$cruise = json_decode($cruise, true);
		}
		if ($cruise && isset($cruise['cruise_from_pax'])) {
			$totalAmount = $cruise['cruise_from_pax']['total'] ?? null;
			$totalPaidAmount = $cruise['cruise_from_pax']['amountPaid'] ?? null;
			$refund = $cruise['cruise_from_pax']['refund'] ?? null;
			$cruiseFromPaxPendingPayment = $cruiseFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}

		$insurance = $this->insurance;
		if (is_string($insurance)) {
			$insurance = json_decode($insurance, true);
		}
		if ($insurance && isset($insurance['insurance_from_pax'])) {
			$totalAmount = $insurance['insurance_from_pax']['total'] ?? null;
			$totalPaidAmount = $insurance['insurance_from_pax']['amountPaid'] ?? null;
			$refund = $insurance['insurance_from_pax']['refund'] ?? null;
			$insuranceFromPaxPendingPayment = $insuranceFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}

		$land_package = $this->land_package;
		if (is_string($land_package)) {
			$land_package = json_decode($land_package, true);
		}
		if ($land_package && isset($land_package['landpackage_from_pax'])) {
			$totalAmount = $land_package['landpackage_from_pax']['total'] ?? null;
			$totalPaidAmount = $land_package['landpackage_from_pax']['amountPaid'] ?? null;
			$refund = $land_package['landpackage_from_pax']['refund'] ?? null;
			$landPackageFromPaxPendingPayment = $landPackageFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}

		$misc = $this->misc;
		if (is_string($misc)) {
			$misc = json_decode($misc, true);
		}
		if ($misc && isset($misc['misc_from_pax'])) {
			$totalAmount = $misc['misc_from_pax']['total'] ?? null;
			$totalPaidAmount = $misc['misc_from_pax']['amountPaid'] ?? null;
			$refund = $misc['misc_from_pax']['refund'] ?? null;
			$miscFromPaxPendingPayment = $miscFromPaxPendingPayment + $totalAmount - $totalPaidAmount - $refund;
		}

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
			//'fromAirport' => new WorldAirportResource($this->whenLoaded('fromAirport')),
			//'toAirport' => new WorldAirportResource($this->whenLoaded('toAirport')),
			// 'hotelFromPaxPendingPayment' => $hotelFromPaxPendingPayment,
			// 'airticketFromPaxPendingPayment' => $airticketFromPaxPendingPayment,
			// 'cruiseFromPaxPendingPayment' => $cruiseFromPaxPendingPayment,
			// 'insuranceFromPaxPendingPayment' => $insuranceFromPaxPendingPayment,
			// 'landPackageFromPaxPendingPayment' => $landPackageFromPaxPendingPayment,
			// 'miscFromPaxPendingPayment' => $miscFromPaxPendingPayment,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
