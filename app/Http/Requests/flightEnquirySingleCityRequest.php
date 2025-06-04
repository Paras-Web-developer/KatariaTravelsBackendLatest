<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class flightEnquirySingleCityRequest extends FormRequest
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
			'id' => 'nullable|integer|exists:enquiries,id',
			'supplier_id' => 'nullable|integer|exists:suppliers,id',
			'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
			'enquiry_source_id' => 'required|integer|exists:enquiry_sources,id',
			'parent_id' => 'nullable|integer|exists:enquiries,id',
			// 'enquiry_payment_status_id' => 'required|integer|exists:enquiry_payment_statuses,id',
			'assigned_to_user_id' => 'required|integer|exists:users,id',
			'enquiry_status_id' => 'required|integer|exists:enquiry_statuses,id',
			'air_line_id' => 'nullable|integer|exists:air_lines,id',
			'budget' => ['nullable', 'numeric'],
			'title' => 'nullable|string|max:255',
			'customer_name' => ['required', 'string', 'max:255'],
			'phone_number' => ['required', 'string', 'max:255'],
			'email' => ['nullable', 'string', 'email', 'max:255'],
			'booking_reference' => 'nullable|string',
			'invoice_number' => 'nullable|string',
			'remark' => 'nullable|string',
			'paid_amount' => ['nullable', 'numeric'],
			'type' => 'nullable|string',
			'package_type' => 'required|string|in:one_way,return_way,multi_city',
			'status' => 'nullable|string|in:pending,accept,reject',
			'from' => 'nullable|string',
			'to' => 'nullable|string',
			'departure_date' => 'required|date',
			'return_date' => 'nullable|date',
			'adult' => 'required|integer',
			'child' => 'required|integer',
			'infant' => 'required|integer',
			'class_of_travel' => 'required|string|in:economy,premium_economy,business',
			// 'enquiry_payment_status' => 'nullable|string|in:pending,paid,over_paid,not_paid',
			'followed_up_at' => 'nullable|date',
		];
	}
}
