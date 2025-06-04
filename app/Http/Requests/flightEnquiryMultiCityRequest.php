<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class flightEnquiryMultiCityRequest extends FormRequest
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
			'supplier_id' => 'nullable|integer|exists:suppliers,id',
			'id' => 'nullable|integer|exists:enquiries,id',
			'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
			'enquiry_source_id' => 'required|integer|exists:enquiry_sources,id',
			//'enquiry_payment_status_id' => 'required|integer|exists:enquiry_payment_statuses,id',
			'assigned_to_user_id' => 'required|integer|exists:users,id',
			'enquiry_status_id' => 'required|integer|exists:enquiry_statuses,id',
			'air_line_id' => 'required|integer|exists:air_lines,id',
			'title' => 'nullable|string|max:255',
			'budget' => ['required', 'numeric'],
			'customer_name' => ['required', 'string', 'max:255'],
			'phone_number' => ['required', 'string', 'max:255'],
			'email' => ['nullable', 'string', 'email', 'max:255'],
			'booking_reference' => 'nullable|string',
			'invoice_number' => 'nullable|string',
			'remark' => 'nullable|string',
			'paid_amount' => ['nullable', 'numeric'],
			'type' => 'nullable|string',
			//'enquiry_payment_status' => 'nullable|string|in:pending,paid,over_paid,not_paid',
			'class_of_travel' => 'required|string|in:economy,premium_economy,business',
			'status' => 'nullable|string|in:pending,accept,reject',
			'adult' => 'nullable|integer',
			'child' => 'nullable|integer',
			'infant' => 'nullable|integer',
			'followed_up_at' => 'nullable|date',
			//package data
			'packages' => 'nullable|array',
			'packages.*.id' => 'nullable|integer|exists:packages,id',
			'packages.*.parent_id' =>  'nullable|integer|exists:packages,id',
			'packages.*.departure_date' => 'required|date',
			'packages.*.from' => 'nullable|string',
			'packages.*.to' => 'nullable|string',
		];
	}
}
