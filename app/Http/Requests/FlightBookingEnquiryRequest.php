<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlightBookingEnquiryRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:flight_booking_enquiries,id',
            'full_name' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'phone' => ['nullable', 'string'],
            'departure_city' => ['required', 'string'],
            'destination_city' => ['nullable', 'string'],
            'travel_date' => ['required', 'date'],
            'return_date' => ['nullable', 'string'],
            'no_of_passengers' => ['required', 'string'],
            'message' => 'nullable|string',
            'status' => 'nullable|string'

        ];
    }
}
