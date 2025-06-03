<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhoneNumberResource extends JsonResource
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
            'enquiry_id' => $this->enquiry_id,
            'hotel_enquiry_id' => $this->hotel_enquiry_id,
            'phone_number' => $this->phone_number,
            'message_id' => $this->message_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
