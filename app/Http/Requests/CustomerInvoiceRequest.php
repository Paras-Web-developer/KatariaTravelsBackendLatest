<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerInvoiceRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array
	{
		return [
			'id' => 'nullable|integer|exists:customer_invoices,id',
			'airLine_id' => 'nullable|integer|exists:air_lines,id',
			'customer_id' => 'nullable|integer|exists:customers,id',
			'invoice_number' => 'nullable|string',
			'booking_date' => 'nullable|date',
			'departure_date' => 'nullable|date',
			'sales_agent' => 'nullable|string',
			'travel_from' => 'nullable|string',
			'travel_to' => 'nullable|string',
			'gds_locator' => 'nullable|string',
			'gds_type' => 'nullable|string',
			'itinerary' => 'nullable|string',

			// Passenger Details as an array
			'passenger_details' => 'required|array|min:1',
			'passenger_details.*.id' => 'nullable|integer|exists:passenger_details,id',
			'passenger_details.*.first_name' => 'required|string',
			'passenger_details.*.last_name' => 'required|string',
			'passenger_details.*.title' => 'nullable|string',
			'passenger_details.*.bill_to' => 'nullable|in:0,1',
			'passenger_details.*.add_insurance' => 'nullable|in:0,1',

			//flight  booking resource
			'flight_details' => 'nullable|array',
			'flight_details.*.id' => 'nullable|integer|exists:flight_details,id',
			'flight_details.*.airLine_id' => 'nullable|integer|exists:air_lines,id',
			'flight_details.*.from' => 'nullable|string',
			'flight_details.*.to' => 'nullable|string',
			'flight_details.*.date' => 'nullable|string',
			'flight_details.*.flight_no' => 'nullable|string',
			'flight_details.*.dep_time' => 'required|string',
			'flight_details.*.arr_time' => 'nullable|string',

			//payment details resource
			'payment_details' => 'nullable|array',
			'payment_details.*.id' => 'nullable|integer|exists:payment_details,id',
			'payment_details.*.payment_details_for' => 'nullable|string|in:pax,supplier',
			'payment_details.*.invoice_type_of_payment' => 'nullable|string|in:flight,insurance,landPackage,cruise,hotel',
			'payment_details.*.adult' => 'nullable|integer',
			'payment_details.*.child' => 'nullable|integer',
			'payment_details.*.inf' => 'nullable|integer',
			'payment_details.*.a_base_fare' => 'nullable|numeric',
			'payment_details.*.a_gst' => 'nullable|numeric',
			'payment_details.*.a_taxes' => 'nullable|numeric',
			'payment_details.*.a_sub_total' => 'nullable|numeric',
			'payment_details.*.c_base_fare' => 'nullable|numeric',
			'payment_details.*.c_gst' => 'nullable|numeric',
			'payment_details.*.c_taxes' => 'nullable|numeric',
			'payment_details.*.c_sub_total' => 'nullable|numeric',
			'payment_details.*.i_base_fare' => 'nullable|numeric',
			'payment_details.*.i_gst' => 'nullable|numeric',
			'payment_details.*.i_taxes' => 'nullable|numeric',
			'payment_details.*.i_sub_total' => 'nullable|numeric',
			'payment_details.*.total_amount' => 'nullable|numeric',
			'payment_details.*.paid_amount' => 'nullable|numeric',
			'payment_details.*.balance' => 'nullable|numeric',
			'payment_details.*.payment_method_type' => 'nullable|string',
			'payment_details.*.commission' => 'nullable|numeric',
			'payment_details.*.payment_recieved_from_pax' => 'nullable|integer|in:1,0',
			'payment_details.*.payment_made_to_supplier' => 'nullable|integer|in:1,0',
			'payment_details.*.commission_recieved_from_supplier' => 'nullable|integer|in:1,0',

			//insurance details
			'insurance_details' => 'nullable|array|min:1',
			'insurance_details.*.id' => 'nullable|integer|exists:insurance_details,id',
			'insurance_details.*.first_name' => 'nullable|json',  // Ensure it's a valid JSON
			'insurance_details.*.first_name.*' => 'nullable|string', // Each first name should be a string inside the JSON array

			'insurance_details.*.last_name' => 'nullable|json',  // Ensure it's a valid JSON
			'insurance_details.*.last_name.*' => 'nullable|string', // Each last name should be a string inside the JSON array

			'insurance_details.*.insurance_provider' => 'nullable|string',
			'insurance_details.*.policy_number' => 'nullable|string',
			'insurance_details.*.effective_date' => 'nullable|string',
			'insurance_details.*.termination_date' => 'nullable|string',
			'insurance_details.*.amount_insured' => 'nullable|string',
			'insurance_details.*.insurance_plan' => 'nullable|string',

			//land_hotel_cruise_package_details
			'hotel_cruise_land_packages' => 'nullable|array|min:1',
			'hotel_cruise_land_packages.*.id' => 'nullable|integer|exists:hotel_cruise_land_packages,id',
			'hotel_cruise_land_packages.*.package_name' => 'nullable|string',
			'hotel_cruise_land_packages.*.country_id' => 'nullable|integer|exists:countries,id',
			'hotel_cruise_land_packages.*.start_date' => 'nullable|date',
			'hotel_cruise_land_packages.*.end_date' => 'nullable|date',
			'hotel_cruise_land_packages.*.operator' => 'nullable|string',
			'hotel_cruise_land_packages.*.package_type' => 'nullable|string|in:flight,cruise,land,package',

		];
	}
}
