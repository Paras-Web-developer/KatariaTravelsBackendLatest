<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelEnquiryRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:hotel_enquires,id',
            'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
            'parent_id' => 'nullable|integer|exists:hotel_enquires,id',
            'enquiry_payment_status' => 'nullable|string|in:pending,paid,over_paid,not_paid',
            'assigned_to_user_id' => 'required|integer|exists:users,id',
            'enquiry_source_id' => 'nullable|integer|exists:enquiry_sources,id',
            'enquiry_status_id' => 'nullable|integer|exists:enquiry_statuses,id',
            'budget' => ['required', 'numeric'],
            'invoice_number' => 'nullable|string',
            'paid_amount' => ['required', 'numeric'],
            'status' => 'nullable|string|in:pending,accept,reject',
            'title' => 'nullable|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'nullable|string|max:15',
            'destination' => 'nullable|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'guest' => 'nullable|integer|min:1',
            'room' => 'nullable|integer|min:1',
            'booking_reference' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:500',
        ];
    }
}
