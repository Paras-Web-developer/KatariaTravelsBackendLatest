<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlightDetailsRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:flight_details,id',
            'airLine_id' => 'nullable|integer|exists:air_lines,id',
            // 'customer_invoice_id' => 'required|integer|exists:customer_invoices,id',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
            'date' => 'nullable|date',
            'flight_no' => 'nullable|string',
            'dep_time' => 'nulable|time',
            'arr_time' => 'nullable|time'
        ];
    }
}
