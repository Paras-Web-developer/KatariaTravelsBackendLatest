<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_invoice_id' => $this->customer_invoice_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'insurance_provider' => $this->insurance_provider,
            'policy_number' => $this->policy_number,
            'effective_date' => $this->effective_date,
            'termination_date' => $this->termination_date,
            'amount_insured' => $this->amount_insured,
            'insurance_plan' => $this->insurance_plan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
