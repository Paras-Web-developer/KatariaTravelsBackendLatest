<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignedFlightOneEnquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'id' => 'required|integer|exists:enquiries,id',
            'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
            'enquiry_source_id' => 'nullable|integer|exists:enquiry_sources,id',
            'parent_id' => 'nullable|integer|exists:enquiries,id',
            // 'enquiry_payment_status' => 'nullable|string|in:pending,paid,over_paid,not_paid',
            //'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'enquiry_status_id' => 'nullable|integer|exists:enquiry_statuses,id',
            'air_line_id' => 'nullable|integer|exists:air_lines,id',
            'budget' => ['nullable', 'numeric'],
            'title' => 'nullable|string|max:255',
            'customer_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'booking_reference' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'remark' => 'nullable|string',
            'paid_amount' => ['nullable', 'numeric'],
            'type' => 'nullable|string',
            'package_type' => 'nullable|string|in:one_way,return_way,multi_city',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'adult' => 'nullable|integer',
            'child' => 'nullable|integer',
            'infant' => 'nullable|integer',
            'class_of_travel' => 'nullable|string|in:economy,premium_economy,business',
            'status' => 'nullable|string|in:pending,accept,reject',
            'followed_up_at' => 'nullable|date',
        ];
    }
}
