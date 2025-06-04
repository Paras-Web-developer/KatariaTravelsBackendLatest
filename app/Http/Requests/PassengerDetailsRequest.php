<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PassengerDetailsRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:passenger_details,id',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'title' => 'nullable|string',
            'bill_to' => 'nullable|in:0,1',
            'add_insurance' => 'nullable|in:0,1'
        ];
    }
}
