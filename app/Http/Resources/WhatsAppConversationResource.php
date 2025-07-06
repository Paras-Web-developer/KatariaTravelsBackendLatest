<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppConversationResource extends JsonResource
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
            'phone_number' => $this->phone_number,
            'name' => $this->name,
            'profile_picture' => $this->profile_picture,
            'type' => $this->type,
            'group_id' => $this->group_id,
            'group_name' => $this->group_name,
            'participants' => $this->participants,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'last_message' => $this->last_message,
            'is_archived' => $this->is_archived,
            'is_muted' => $this->is_muted,
            'unread_count' => $this->getUnreadCount(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'latest_message' => $this->whenLoaded('messages', function () {
                return $this->messages->first();
            }),
        ];
    }
}