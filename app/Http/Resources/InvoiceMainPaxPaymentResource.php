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
			$totalAmount = $airticket['airticket_from_pax']['total'] ?? 0;
			$totalPaidAmount = $airticket['airticket_from_pax']['amountPaid'] ?? 0;
			$refund = $airticket['airticket_from_pax']['refund'] ?? 0;
			$airticketFromPaxPendingPayment = $airticketFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}


		$hotel = $this->hotel;
		if (is_string($hotel)) {
			$hotel = json_decode($hotel, true);
		}
		if ($hotel && isset($hotel['hotel_from_pax'])) {
			$totalAmount = $hotel['hotel_from_pax']['total'] ?? 0;
			$totalPaidAmount = $hotel['hotel_from_pax']['amountPaid'] ?? 0;
			$refund = $hotel['hotel_from_pax']['refund'] ?? 0;
			$hotelFromPaxPendingPayment = $hotelFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}

		$cruise = $this->cruise;
		if (is_string($cruise)) {
			$cruise = json_decode($cruise, true);
		}
		if ($cruise && isset($cruise['cruise_from_pax'])) {
			$totalAmount = $cruise['cruise_from_pax']['total'] ?? 0;
			$totalPaidAmount = $cruise['cruise_from_pax']['amountPaid'] ?? 0;
			$refund = $cruise['cruise_from_pax']['refund'] ?? 0;
			$cruiseFromPaxPendingPayment = $cruiseFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}

		$insurance = $this->insurance;
		if (is_string($insurance)) {
			$insurance = json_decode($insurance, true);
		}
		if ($insurance && isset($insurance['insurance_from_pax'])) {
			$totalAmount = $insurance['insurance_from_pax']['total'] ?? 0;
			$totalPaidAmount = $insurance['insurance_from_pax']['amountPaid'] ?? 0;
			$refund = $insurance['insurance_from_pax']['refund'] ?? 0;
			$insuranceFromPaxPendingPayment = $insuranceFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}

		$land_package = $this->land_package;
		if (is_string($land_package)) {
			$land_package = json_decode($land_package, true);
		}
		if ($land_package && isset($land_package['landpackage_from_pax'])) {
			$totalAmount = $land_package['landpackage_from_pax']['total'] ?? 0;
			$totalPaidAmount = $land_package['landpackage_from_pax']['amountPaid'] ?? 0;
			$refund = $land_package['landpackage_from_pax']['refund'] ?? 0;
			$landPackageFromPaxPendingPayment = $landPackageFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}

		$misc = $this->misc;
		if (is_string($misc)) {
			$misc = json_decode($misc, true);
		}
		if ($misc && isset($misc['misc_from_pax'])) {
			$totalAmount = $misc['misc_from_pax']['total'] ?? 0;
			$totalPaidAmount = $misc['misc_from_pax']['amountPaid'] ?? 0;
			$refund = $misc['misc_from_pax']['refund'] ?? 0;
			$miscFromPaxPendingPayment = $miscFromPaxPendingPayment + $totalAmount - $totalPaidAmount -$refund;
		}


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
			'hotelFromPaxPendingPayment' => $this->hotelFromPaxPendingPayment,
			'airticketFromPaxPendingPayment' => $this->airticketFromPaxPendingPayment,
			'cruiseFromPaxPendingPayment' => $this->cruiseFromPaxPendingPayment,
			'insuranceFromPaxPendingPayment' => $this->insuranceFromPaxPendingPayment,
			'landPackageFromPaxPendingPayment' => $this->landPackageFromPaxPendingPayment,
			'miscFromPaxPendingPayment' => $this->miscFromPaxPendingPayment,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
