<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelEnquireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $enquiryPaymentStatus = 'not_paid';
        if ($this->paid_amount > 0) {
            if ($this->paid_amount < $this->budget) {
                $enquiryPaymentStatus = 'pending';
            } elseif ($this->paid_amount == $this->budget) {
                $enquiryPaymentStatus = 'paid';
            } elseif ($this->paid_amount > $this->budget) {
                $enquiryPaymentStatus = 'over_paid';
            }
        }

        return [
            'id' => $this->id,
            'enquiry_code' => $this->enquiry_code,
            'transaction_type_id' => $this->transaction_type_id,
            'transactionType' => new TransactionTypeResource($this->whenLoaded('transactionType')),
            'enquiry_status_id' => $this->enquiry_status_id,
            'enquiryStatus' => new EnquiryStatusResource($this->whenLoaded('enquiryStatus')),
            'updated_by_user_id' => $this->updated_by_user_id,
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            'parent_id' => $this->parent_id,
            'parent' => new HotelEnquireResource($this->whenLoaded('parent')),
            'children' => HotelEnquireResource::collection($this->whenLoaded('children')),
            'created_by_user_id' => $this->created_by_user_id,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'enquiry_source_id' => $this->enquiry_source_id,
            'enquirySource'  => new EnquirySourceResource($this->whenLoaded('enquirySource')),
            'enquiry_payment_status' => $this->enquiry_payment_status,
            'enquiryPaymentStatus' => new EnquiryPaymentStatusResource($this->whenLoaded('enquiryPaymentStatus')),
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'assignedToUser' => new UserResource($this->whenLoaded('assignedToUser')),
            'budget' => $this->budget,
            'pending_amount' => $this->pendingAmount ?? max(0, $this->budget - $this->paid_amount),
            'invoice_number' => $this->invoice_number,
            'paid_amount' => $this->paid_amount,
            'payment_details' => [
                'pending_amount' => $this->budget && $this->paid_amount
                    ? max(0, $this->budget - $this->paid_amount)
                    : 0,
                'overPaidAmount' => $this->budget && $this->paid_amount
                    ? max(0, $this->paid_amount - $this->budget)
                    : 0,
            ],
            'status' => $this->status,
            'title' => $this->title,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'destination' => $this->destination,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'guest' => $this->guest,
            'room' => $this->room,
            'booking_reference' => $this->booking_reference,
            'special_requests' => $this->special_requests,
            'admin_payment_status' => $this->admin_payment_status,
            'note' => $this->note,
            'follow_up_message' => $this->follow_up_message,
            'follow_up_at' => $this->follow_up_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
