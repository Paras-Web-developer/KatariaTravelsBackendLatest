<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentDetailResource extends JsonResource
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
            'payment_details_for' => $this->payment_details_for,
            'invoice_type_of_payment' => $this->invoice_type_of_payment,
            'adult' => $this->adult,
            'child' => $this->child,
            'inf' => $this->inf,
            'a_base_fare' => $this->a_base_fare,
            'a_gst' => $this->a_gst,
            'a_taxes' => $this->a_taxes,
            'a_sub_total' => $this->a_sub_total,
            'c_base_fare' => $this->c_base_fare,
            'c_gst' => $this->c_gst,
            'c_taxes' => $this->c_taxes,
            'c_sub_total' => $this->c_sub_total,
            'i_base_fare' => $this->i_base_fare,
            'i_gst' => $this->i_gst,
            'i_taxes' => $this->i_taxes,
            'i_sub_total' => $this->i_sub_total,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance' => $this->balance,
            'payment_method_type' => $this->payment_method_type,
            'commission' => $this->commission,
            'payment_recieved_from_pax' => $this->payment_recieved_from_pax,
            'payment_made_to_supplier' => $this->payment_made_to_supplier,
            'commission_recieved_from_supplier' => $this->commission_recieved_from_supplier,
            'payment_method_type' => $this->payment_method_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
