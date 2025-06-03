<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'transaction_type_id' => $this->transaction_type_id,
            'transactionType' => new TransactionTypeResource($this->whenLoaded('transactionType')),
            'agent_user_id' => $this->agent_user_id,
            'agentUser' => new UserResource($this->whenLoaded('agentUser')),
            'supplier_id' => $this->supplier_id,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'enquiry_id' => $this->enquiry_id,
            'enquiry' => new EnquiryResource($this->whenLoaded('enquiry')),
            'airLine_id' => $this->airLine_id,
            'airLine' => new AirLineResource($this->whenLoaded('airLine')),
            'invoice_number' => $this->invoice_number,
            'date' => $this->date,
            'pnr' => $this->pnr,
            'cost' => $this->cost,
            'sold_fare' => $this->sold_fare,
            'amex_card' => $this->amex_card,
            'cibc_card' => $this->cibc_card,
            'td_busness_visa_card' => $this->td_busness_visa_card,
            'bmo_master_card' => $this->bmo_master_card,
            'rajni_mam' => $this->rajni_mam,
            'td_fc_visa' => $this->td_fc_visa,
            'ch_eq_ue' => $this->ch_eq_ue,
            'ticket_number' => $this->ticket_number,
            'fnu' => $this->fnu,
            'ticket_status' => $this->ticket_status,
            'reference_number_of_et' => $this->reference_number_of_et,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
