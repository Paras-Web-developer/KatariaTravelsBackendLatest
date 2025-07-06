<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppMessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'message_id' => $this->message_id,
            'direction' => $this->direction,
            'type' => $this->type,
            'content' => $this->content,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'is_media' => $this->isMedia(),
            'is_text' => $this->isText(),
            'conversation' => $this->whenLoaded('conversation', function () {
                return new WhatsAppConversationResource($this->conversation);
            }),
        ];
    }
}