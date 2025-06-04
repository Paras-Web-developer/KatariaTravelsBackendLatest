<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignOtherServiceEnquiryRequest extends FormRequest
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
            'id' => 'required|integer|exists:other_services,id',
            'parent_id' => 'nullable|integer|exists:other_services,id',
            'enquiry_source_id' => 'nullable|integer|exists:enquiry_sources,id',
            'enquiry_status_id' => 'nullable|integer|exists:enquiry_statuses,id',
            'assigned_to_user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string',
            'customer_name' => 'nullable|string',
            'email' => 'required|string',
            'phone_number' => 'required|string',
            'price_quote' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'booking_reference_no' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'service_name' => 'required|string',
            'status' => 'nullable|string|in:pending,accept,reject',
            'note' => 'nullable|string',
        ];
    }
}
