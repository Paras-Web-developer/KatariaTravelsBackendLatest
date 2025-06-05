<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_type_id' => $this->transaction_type_id,
            'transactionType' => new TransactionTypeResource($this->whenLoaded('transactionType')),

            'transaction_type_agency_id' => $this->transaction_type_agency_id,
            'transactionTypeAgency' => new TransactionTypeResource($this->whenLoaded('transactionTypeAgency')),

            'agent_user_id' => $this->agent_user_id,
            'agentUser' => new UserResource($this->whenLoaded('agentUser')),

            'supplier_id' => $this->supplier_id,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),

            'enquiry_id' => $this->enquiry_id,
            'enquiry' => new EnquiryResource($this->whenLoaded('enquiry')),

            'airLine_id' => $this->airLine_id,
            'airLine' => new AirLineResource($this->whenLoaded('airLine')),

            'invoice_number' => $this->invoice_number,
            'invoice_holder_name' => $this->invoice_holder_name,
            'tickets' => $this->tickets,
            'date' => $this->date,
            'temp_supplier' => $this->temp_supplier,
            'pnr' => $this->pnr,
            'ch_eq_ue' => $this->ch_eq_ue,
            'ticket_status' => $this->ticket_status,
            'reference_number_of_et' => $this->reference_number_of_et,
            'remarks' => $this->remarks,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
