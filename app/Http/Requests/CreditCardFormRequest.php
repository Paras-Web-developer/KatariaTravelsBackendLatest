<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CreditCardFormRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:credit_card_forms,id',
            'airLine_id' => 'nullable|integer|exists:air_lines,id',
            'holder_name' => 'required|string',
            'type' => 'nullable|string',
            'card_number' => 'required|integer',
            'card_number' => [
                        'required', 
                        'string', 
                        'digits:16',
                        Rule::unique('credit_card_forms', 'card_number')->ignore($this->id),
            ],
            'expire_date' => [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
            ],
            //'cvv' => 'required|integer',
            'cvv' => 'required|integer|digits_between:3,4',
            'amount' => 'nullable|numeric',
            'travel_date' => 'nullable|date',
            'transportation' => 'nullable|string',
            'country' => 'required|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|integer',
            'phone_number' => 'nullable|string',
            'signature' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg,jpg,webp|max:2048',
        ];
    }
}
