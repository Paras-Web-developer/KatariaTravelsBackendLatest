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

class InvoiceMainCustomResource extends JsonResource
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
		// Helper: Add transaction type name to an array if it has "transation_type_id".
		// ---------------------------
		$addTransactionTypeName = function (&$array) {
			if (isset($array['transation_type_id'])) {
				$transactionType = TransactionType::find($array['transation_type_id']);
				$array['transaction_type_name'] = $transactionType ? $transactionType->name : null;
			}
		};

		// ---------------------------
		// Process the land_package field
		// ---------------------------
		$landPackage = $this->land_package;
		if (is_string($landPackage)) {
			$landPackage = json_decode($landPackage, true);
		}
		// Get nested landPackageDetails and country name.
		$landPackageDetails = $landPackage['landPackageDetails'] ?? null;
		$landCountryName = null;
		if ($landPackageDetails && isset($landPackageDetails['country'])) {
			$countryRec = Country::find($landPackageDetails['country']);
			//dd($countryRec);
			$landCountryName = $countryRec ? $countryRec->name : null;
			$landPackage['country_name'] = $landCountryName;
		}



		$totalAmount = 0;
		$totalCommission = 0;
		$totalHotelAmount = 0;
		$totalHotelCommission = 0;

		$totalairTicketAmount = 0;
		$totalairTicketCommission = 0;

		$totalCruiseAmount = 0;
		$totalCruiseCommission = 0;

		$totalInsuranceAmount = 0;
		$totalInsuranceCommission = 0;

		$totalLandPackageAmount = 0;
		$totalLandPackageCommission = 0;

		$totalMiscAmount = 0;
		$totalMiscCommission = 0;

		$hotelFromPaxPaymentMethodId = 0;
		$hotelFromSupplierPaymentMethodId = 0;

		$airticketFromPaxPaymentMethodId = 0;
		$airticketFromSupplierPaymentMethodId = 0;

		$cruiseFromPaxPaymentMethodId = 0;
		$cruiseFromSupplierPaymentMethodId = 0;

		$insuranceFromPaxPaymentMethodId = 0;
		$insuranceFromSupplierPaymentMethodId = 0;

		$landPackageFromPaxPaymentMethodId = 0;
		$landPackageFromSupplierPaymentMethodId = 0;

		$miscFromPaxPaymentMethodId = 0;
		$miscFromSupplierPaymentMethodId = 0;

		// ---------------------------
		// Process the hotel field
		// ---------------------------
		$hotel = $this->hotel;
		if (is_string($hotel)) {
			$hotel = json_decode($hotel, true);
		}
		if ($hotel && isset($hotel['hotel_from_pax'])) {
			$totalHotelAmount = $hotel['hotel_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalHotelAmount;
			$totalHotelCommission = $hotel['hotel_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalHotelCommission;
			$hotelFromPaxPaymentMethodId = $hotel['hotel_from_pax']['transation_type_id'] ?? null;
		}
		if ($hotel && isset($hotel['hotel_to_supplier'])) {
			$hotelFromSupplierPaymentMethodId = $hotel['hotel_to_supplier']['transation_type_id'] ?? null;
		}

		$airticket = $this->airticket;
		if (is_string($airticket)) {
			$airticket = json_decode($airticket, true);
		}
		if ($airticket && isset($airticket['airticket_from_pax'])) {
			$totalairTicketAmount = $airticket['airticket_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalairTicketAmount;
			$totalairTicketCommission = $airticket['airticket_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalairTicketCommission;
			$airticketFromPaxPaymentMethodId = $airticket['airticket_from_pax']['transation_type_id'] ?? null;
		}

		if ($airticket && isset($airticket['airticket_to_supplier'])) {
			$airticketFromSupplierPaymentMethodId = $airticket['airticket_to_supplier']['transation_type_id'] ?? null;
		}

		$cruise = $this->cruise;
		if (is_string($cruise)) {
			$cruise = json_decode($cruise, true);
		}
		if ($cruise && isset($cruise['cruise_from_pax'])) {
			$totalCruiseAmount = $cruise['cruise_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalCruiseAmount;
			$totalCruiseCommission = $cruise['cruise_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalCruiseCommission;
			$cruiseFromPaxPaymentMethodId = $cruise['cruise_from_pax']['transation_type_id'] ?? null;
		}

		if ($cruise && isset($cruise['cruise_to_supplier'])) {
			$cruiseFromSupplierPaymentMethodId = $cruise['cruise_to_supplier']['transation_type_id'] ?? null;
		}

		$insurance = $this->insurance;
		if (is_string($insurance)) {
			$insurance = json_decode($insurance, true);
		}
		if ($insurance && isset($insurance['insurance_from_pax'])) {
			$totalInsuranceAmount = $insurance['insurance_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalInsuranceAmount;
			$totalInsuranceCommission = $insurance['insurance_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalInsuranceCommission;
			$insuranceFromPaxPaymentMethodId = $insurance['insurance_from_pax']['transation_type_id'] ?? null;

		}
		if ($insurance && isset($insurance['insurance_to_supplier'])) {
			$insuranceFromSupplierPaymentMethodId = $cruise['insurance_to_supplier']['transation_type_id'] ?? null;
		}

		$land_package = $this->land_package;
		if (is_string($land_package)) {
			$land_package = json_decode($land_package, true);
		}
		if ($land_package && isset($land_package['landpackage_from_pax'])) {
			$totalLandPackageAmount = $land_package['landpackage_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalLandPackageAmount;
			$totalLandPackageCommission = $land_package['landpackage_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalLandPackageCommission;
			$landPackageFromPaxPaymentMethodId =   $land_package['landpackage_from_pax']['transation_type_id'] ?? null;
		}

		if ($land_package && isset($land_package['landpackage_to_supplier'])) {
			$landPackageFromSupplierPaymentMethodId = $land_package['landpackage_to_supplier']['transation_type_id'] ?? null;
		}

		$misc = $this->misc;
		if (is_string($misc)) {
			$misc = json_decode($misc, true);
		}
		if ($land_package && isset($misc['misc_from_pax'])) {
			$totalMiscAmount = $misc['misc_from_pax']['total'] ?? null;
			$totalAmount = $totalAmount + $totalMiscAmount;
			$totalMiscCommission = $misc['misc_from_pax']['commission'] ?? null;
			$totalCommission = $totalCommission + $totalMiscCommission;
			$miscFromPaxPaymentMethodId =   $misc['misc_from_pax']['transation_type_id'] ?? null;
		}

		if ($misc && isset($misc['misc_to_supplier'])) {
			$miscFromSupplierPaymentMethodId = $misc['misc_to_supplier']['transation_type_id'] ?? null;
		}

		//dd($totalAmount , $totalCommission);



		// ---------------------------
		// Build and return the resource array.
		// ---------------------------
		return [
			'id'                         => $this->id,
			'sales_agent_id'             => $this->sales_agent_id,
			'sales_agents'               => new SalesAgentCustomResource($this->whenLoaded('sales_agents')),
			'airLine_id'                 => $this->airLine_id,
			'airLine'                    => new AirLineResource($this->whenLoaded('airLine')),
			'customer_id'                => $this->customer_id,
			'customer'                   => new CustomerResource($this->whenLoaded('customer')),
			//'customer_details'           => $topCustomerDetails,
			'supplier_id'                => $this->supplier_id,
			'supplier'                   => new SupplierResource($this->whenLoaded('supplier')),

			'totalHotelAmount' 	   => $totalHotelAmount,
			'totalHotelCommission' => $totalHotelCommission,

			'totalairTicketAmount' => $totalairTicketAmount,
			'totalairTicketCommission' => $totalairTicketCommission,

			'totalCruiseAmount' => $totalCruiseAmount,
			'totalCruiseCommission' => $totalCruiseCommission,

			'totalInsuranceAmount' => $totalInsuranceAmount,
			'totalInsuranceCommission' => $totalInsuranceCommission,

			'totalLandPackageAmount' => $totalLandPackageAmount,
			'totalLandPackageCommission' => $totalLandPackageCommission,

			'totalMiscAmount' => $totalMiscAmount,
			'totalMiscCommission' => $totalMiscCommission,

			'fromAirport' => new WorldAirportResource($this->whenLoaded('fromAirport')),
			'toAirport' => new WorldAirportResource($this->whenLoaded('toAirport')),
			'invoice_number'             => $this->invoice_number,
			'booking_date'               => $this->booking_date,
			'passenger_details'          => $this->passenger_details,
			'totalAmount'                => $totalAmount,
			'totalCommission'            => $totalCommission,

			'hotelFromPaxPaymentMethodId' => $hotelFromPaxPaymentMethodId,
			'airticketFromPaxPaymentMethodId' => $airticketFromPaxPaymentMethodId,
			'cruiseFromPaxPaymentMethodId' => $cruiseFromPaxPaymentMethodId,
			'insuranceFromPaxPaymentMethodId' => $insuranceFromPaxPaymentMethodId,
			'landPackageFromPaxPaymentMethodId' => $landPackageFromPaxPaymentMethodId,
			'miscFromPaxPaymentMethodId' => $miscFromPaxPaymentMethodId,

			'hotelFromSupplierPaymentMethodId' => $hotelFromSupplierPaymentMethodId,
			'airticketFromSupplierPaymentMethodId' => $airticketFromSupplierPaymentMethodId,
			'cruiseFromSupplierPaymentMethodId' => $cruiseFromSupplierPaymentMethodId,
			'insuranceFromSupplierPaymentMethodId' => $insuranceFromSupplierPaymentMethodId,
			'landPackageFromSupplierPaymentMethodId' => $landPackageFromSupplierPaymentMethodId,
			'miscFromSupplierPaymentMethodId' => $miscFromSupplierPaymentMethodId,

			'created_at'                 => $this->created_at,
			'updated_at'                 => $this->updated_at,
		];
	}
}
