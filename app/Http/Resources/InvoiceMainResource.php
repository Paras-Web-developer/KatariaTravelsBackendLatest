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

class InvoiceMainResource extends JsonResource
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

		// Process transaction type on land_package "pax" and "supplier" parts.
		if (isset($landPackage['landpackage_from_pax'])) {
			$addTransactionTypeName($landPackage['landpackage_from_pax']);
		}
		if (isset($landPackage['landpackage_to_supplier'])) {
			$addTransactionTypeName($landPackage['landpackage_to_supplier']);
		}
		// Also, if your land_package data is under a "details" key:
		if (isset($landPackage['details']['landpackage_from_pax'])) {
			$addTransactionTypeName($landPackage['details']['landpackage_from_pax']);
		}
		if (isset($landPackage['details']['landpackage_to_supplier'])) {
			$addTransactionTypeName($landPackage['details']['landpackage_to_supplier']);
		}

		// ---------------------------
		// Process the airticket field
		// ---------------------------
		$airticket = $this->airticket;
		if (is_string($airticket)) {
			$airticket = json_decode($airticket, true);
		}

		$airticketDetails = $airticket['airticketDetails'] ?? null;
		$airticketCountryName = null;
		if ($airticketDetails && isset($airticketDetails['country'])) {
			$countryRec = Country::find($airticketDetails['country']);
			//dd($countryRec);
			$airticketCountryName = $countryRec ? $countryRec->name : null;
			$airticket['country_name'] = $airticketCountryName;
		}

		// Get airline name from airticket['airLine_id'].
		$airLineId = $airticket['airLine_id'] ?? null;
		$airlineName = null;
		if ($airLineId) {
			$airLine = AirLine::find($airLineId);
			$airlineName = $airLine ? $airLine->airline_name : null;
		}
		$airticketSupplierId = $airticket['airticket_supplier_id'] ?? null;
		$airticketSupplierName = null;
		if ($airticketSupplierId) {
			$airticketSupplier = Supplier::find($airticketSupplierId);
			$airticketSupplierName = $airticketSupplier ? $airticketSupplier->name : null;
		}
		$airticket['airticket_supplier_name'] = $airticketSupplierName;
		$airticket['airline_name'] = $airlineName;
		// Process transaction types in airticket's pax and supplier arrays.
		if (isset($airticket['airticket_from_pax'])) {
			$addTransactionTypeName($airticket['airticket_from_pax']);
		}
		if (isset($airticket['airticket_to_supplier'])) {
			$addTransactionTypeName($airticket['airticket_to_supplier']);
		}

		// ---------------------------
		// Process the insurance field
		// ---------------------------
		$insurance = $this->insurance;
		if (is_string($insurance)) {
			$insurance = json_decode($insurance, true);
		}
		if ($insurance && isset($insurance['insurance_from_pax'])) {
			$addTransactionTypeName($insurance['insurance_from_pax']);
		}
		if ($insurance && isset($insurance['insurance_to_supplier'])) {
			$addTransactionTypeName($insurance['insurance_to_supplier']);
		}

		// ---------------------------
		// Process the cruise field
		// ---------------------------
		$cruise = $this->cruise;
		if (is_string($cruise)) {
			$cruise = json_decode($cruise, true);
		}
		if ($cruise && isset($cruise['cruise_from_pax'])) {
			$addTransactionTypeName($cruise['cruise_from_pax']);
		}
		if ($cruise && isset($cruise['cruise_to_supplier'])) {
			$addTransactionTypeName($cruise['cruise_to_supplier']);
		}

		$cruiseDetails = $cruise['cruiseDetails'] ?? null;
		$cruiseCountryName = null;
		if ($cruiseDetails && isset($cruiseDetails['country'])) {
			$countryRec = Country::find($cruiseDetails['country']);
			//dd($countryRec);
			$cruiseCountryName = $countryRec ? $countryRec->name : null;
			$cruise['country_name'] = $cruiseCountryName;
		}

		// ---------------------------
		// Process the hotel field
		// ---------------------------
		$hotel = $this->hotel;
		if (is_string($hotel)) {
			$hotel = json_decode($hotel, true);
		}
		if ($hotel && isset($hotel['hotel_from_pax'])) {
			$addTransactionTypeName($hotel['hotel_from_pax']);
		}
		if ($hotel && isset($hotel['hotel_to_supplier'])) {
			$addTransactionTypeName($hotel['hotel_to_supplier']);
		}
		$hotelDetails = $hotel['hotelDetails'] ?? null;
		$hotelCountryName = null;
		if ($hotelDetails && isset($hotelDetails['country'])) {
			$countryRec = Country::find($hotelDetails['country']);
			//dd($countryRec);
			$hotelCountryName = $countryRec ? $countryRec->name : null;
			$hotel['country_name'] = $hotelCountryName;
		}

		// ---------------------------
		// Process the misc field
		// ---------------------------
		$misc = $this->misc;
		if (is_string($misc)) {
			$misc = json_decode($misc, true);
		}
		if ($misc && isset($misc['misc_from_pax'])) {
			$addTransactionTypeName($misc['misc_from_pax']);
		}
		if ($misc && isset($misc['misc_to_supplier'])) {
			$addTransactionTypeName($misc['misc_to_supplier']);
		}

		$miscDetails = $misc['miscDetails'] ?? null;
		$miscCountryName = null;
		if ($miscDetails && isset($miscDetails['country'])) {
			$countryRec = Country::find($miscDetails['country']);
			//dd($countryRec);
			$miscCountryName = $countryRec ? $countryRec->name : null;
			$misc['country_name'] = $miscCountryName;
		}

		$miscSupplierId = $misc['misc_supplier_id'] ?? null;
		$miscSupplierName = null;
		if ($miscSupplierId) {
			$miscSupplier = Supplier::find($miscSupplierId);
			$miscSupplierName = $miscSupplier ? $miscSupplier->name : null;
		}
		$misc['misc_supplier_name'] = $miscSupplierName;

		// ---------------------------
		// Process the top-level customer_details field
		// ---------------------------
		$topCustomerDetails = $this->customer_details;
		if (is_string($topCustomerDetails)) {
			$topCustomerDetails = json_decode($topCustomerDetails, true);
		}
		$topCityName = null;
		$topCountryName = null;
		$topStateName = null;
		if ($topCustomerDetails) {
			if (isset($topCustomerDetails['city_id'])) {
				$city = City::find($topCustomerDetails['city_id']);
				$topCityName = $city ? $city->name : null;
			}
			if (isset($topCustomerDetails['country_id'])) {
				$country = Country::find($topCustomerDetails['country_id']);
				$topCountryName = $country ? $country->name : null;
			}
			if (isset($topCustomerDetails['state_id'])) {
				$state = State::find($topCustomerDetails['state_id']);
				$topStateName = $state ? $state->name : null;
			}
			$topCustomerDetails['city_name'] = $topCityName;
			$topCustomerDetails['country_name'] = $topCountryName;
			$topCustomerDetails['state_name'] = $topStateName;
		}

		// ---------------------------
		// Build and return the resource array.
		// ---------------------------
		return [
			'id'                         => $this->id,
			'invoice_id'   => $this->invoice_id,
			'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
 			'updated_by_user_id' => $this->updated_by_user_id,
			'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
			'created_by_user_id' => $this->created_by_user_id,
			'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
			'parent_id'                  => $this->parent_id,
			'parent'                     => new InvoiceMainResource($this->whenLoaded('parent')),
			'children'                   => InvoiceMainResource::collection($this->whenLoaded('children')),
			'sales_agent_id'             => $this->sales_agent_id,
			'sales_agents'               => new UserResource($this->whenLoaded('sales_agents')),
			'hotel_enquire_id'           => $this->hotel_enquire_id,
			'hotelEnquiry'               => new HotelEnquireResource($this->whenLoaded('hotelEnquiry')),
			'flight_enquiry_id'          => $this->flight_enquiry_id,
			'flightEnquiry'              => new EnquiryResource($this->whenLoaded('flightEnquiry')),
			'airLine_id'                 => $this->airLine_id,
			'airLine'                    => new AirLineResource($this->whenLoaded('airLine')),
			'customer_id'                => $this->customer_id,
			'customer_details'           => $topCustomerDetails,
			'supplier_id'                => $this->supplier_id,
			'supplier'                   => new SupplierResource($this->whenLoaded('supplier')),
			'invoice_number'             => $this->invoice_number,
			'itinerary'                  => $this->itinerary,
			'gds_type'                   => $this->gds_type,
			'gds_locator'                => $this->gds_locator,
			'booking_date'               => $this->booking_date,
			'departure_date'             => $this->departure_date,
			'ticket_number'              => $this->ticket_number,
			'mco'                        => $this->mco,
			'status'                     => $this->status,
			'pdf_path'					 => $this->pdf_path ? asset($this->pdf_path) : null,
			'travel_from'                => $this->travel_from,
			'travel_to'                  => $this->travel_to,
			'fromAirport' => new WorldAirportResource($this->whenLoaded('fromAirport')),
			'toAirport' => new WorldAirportResource($this->whenLoaded('toAirport')),
			'passenger_details'          => $this->passenger_details,
			'airticket'                  => $airticket,
			'cruise'                     => $cruise,
			'hotel'                      => $hotel,
			'insurance'                  => $insurance,
			// 'land_package'               => [
			//     'details'      => $landPackage,
			//     'country_name' => $landCountryName,
			// ],
			'land_package'               => $landPackage,
			'misc'                       => $misc,
			'valid_canadian_passport'    => $this->valid_canadian_passport,
			'valid_travel_visa'          => $this->valid_travel_visa,
			'tourist_card'               => $this->tourist_card,
			'canadian_citizenship_or_prCard' => $this->canadian_citizenship_or_prCard,
			'special_remarks'            => $this->special_remarks,
			'other_remarks'              => $this->other_remarks,
			'airticket_include'          => $this->airticket_include,
			'insurance_include'          => $this->insurance_include,
			'misc_include'               => $this->misc_include,
			'land_package_include'       => $this->land_package_include,
			'hotel_include'              => $this->hotel_include,
			'cruise_include'             => $this->cruise_include,
			'created_at'                 => $this->created_at,
			'updated_at'                 => $this->updated_at,
		];
	}
}
