<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
			'id' => 'nullable|integer|exists:customers,id',
			'country_id' => 'nullable|integer|exists:countries,id',
			'state_id' => 'nullable|integer|exists:states,id',
			'city_id' => 'nullable|integer|exists:cities,id',
			'title' => 'nullable|string|max:255',
			'fax' => 'nullable|string|max:255',
			'company_name' => 'nullable|string|max:255',

			'full_name' => 'required|string|max:255',
			'email' => [
				'nullable',
				'email',
				'max:255',
			],
			'phone_number' => ['required', 'string', 'max:255', Rule::unique('customers', 'phone_number')->ignore($this->id),],
			'alternate_phone' => ['nullable', 'string', 'max:255'],
			'address' => ['nullable', 'string'],
			'state' => ['nullable', 'string'],
			'city' => ['nullable', 'string'],
			'postal_code' => ['nullable', 'string'],
			'dob' => ['nullable', 'string'],
			'gender' => ['nullable', 'string'],
			// 'languages' => ['nullable', 'array'],
			'languages' => ['nullable', 'string', 'max:255'],
			'medical_information' => ['nullable', 'string'],
			'passport_number' => ['nullable', 'string'],
		];
	}
}
