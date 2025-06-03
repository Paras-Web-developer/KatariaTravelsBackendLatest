<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OtherServiceResource extends JsonResource
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
            'created_by_user_id' => $this->created_by_user_id,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'updated_by_user_id' => $this->updated_by_user_id,
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            'enquiry_code' => $this->enquiry_code,
            'parent_id' => $this->parent_id,
            'parent' => new OtherServiceResource($this->whenLoaded('parent')),
            'children' => OtherServiceResource::collection($this->whenLoaded('children')),
            'enquiry_source_id' => $this->enquiry_source_id,
            'enquirySource' => new EnquirySourceResource($this->whenLoaded('enquirySource')),
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'assignedUser' => new UserResource($this->whenLoaded('assignedUser')),
            'enquiry_status_id' => $this->enquiry_status_id,
            'enquiryStatus' => new EnquiryStatusResource($this->whenLoaded('enquiryStatus')),
            'title' => $this->title,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'price_quote' => $this->price_quote,
            'paid_amount' => $this->paid_amount,
            'payment_details' => [
                'pending_amount' => $this->price_quote && $this->paid_amount
                    ? max(0, $this->price_quote - $this->paid_amount)
                    : 0,
                'overPaidAmount' => $this->price_quote && $this->paid_amount
                    ? max(0, $this->paid_amount - $this->price_quote)
                    : 0,
            ],
            'invoice_number' => $this->invoice_number,
            'booking_reference_no' => $this->booking_reference_no,
            'special_requests' => $this->special_requests,
            'service_name' => $this->service_name,
            'status' => $this->status,
            'enquiry_payment_status' => $this->enquiry_payment_status,
            'admin_payment_status' => $this->admin_payment_status,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
