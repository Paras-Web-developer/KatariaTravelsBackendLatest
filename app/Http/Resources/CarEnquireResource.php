<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarEnquireResource extends JsonResource
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
            'enquiry_status_id' => $this->enquiry_status_id,
            'enquiryStatus' => new EnquiryStatusResource($this->whenLoaded('enquiryStatus')),
            'updated_by_user_id' => $this->updated_by_user_id,
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            'parent_id' => $this->parent_id,
            'parent' => new CarEnquireResource($this->whenLoaded('parent')),
            'children' => CarEnquireResource::collection($this->whenLoaded('children')),
            'created_by_user_id' => $this->created_by_user_id,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'enquiry_source_id' => $this->enquiry_source_id,
            'enquirySource'  => new EnquirySourceResource($this->whenLoaded('enquirySource')),
            'enquiry_payment_status_id' => $this->enquiry_payment_status_id,
            'enquiryPaymentStatus' => new EnquiryPaymentStatusResource($this->whenLoaded('enquiryPaymentStatus')),
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'assignedToUser' => new UserResource($this->whenLoaded('assignedToUser')),
             'budget' => $this->budget,
            'invoice_number' => $this->invoice_number,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status,
            'car_type_id' => $this->car_type_id,
            'carType' => new CarTypeResource($this->whenLoaded('carType')),
            'enquiry_type' => $this->enquiry_type,
            'pick_up_location' => $this->pick_up_location,
            'drop_off_location' => $this->drop_off_location,
            'pick_up_date' => $this->pick_up_date,
            'drop_off_date' => $this->drop_off_date,
            'title' => $this->title,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'special_requests' => $this->special_requests,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
