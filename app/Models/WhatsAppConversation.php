<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'phone_number',
        'name',
        'profile_picture',
        'type',
        'group_id',
        'group_name',
        'participants',
        'last_message_at',
        'last_message',
        'is_archived',
        'is_muted',
    ];

    protected $casts = [
        'participants' => 'array',
        'last_message_at' => 'datetime',
        'is_archived' => 'boolean',
        'is_muted' => 'boolean',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function getLatestMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function getUnreadCount()
    {
        return $this->messages()
            ->where('direction', 'inbound')
            ->where('status', '!=', 'read')
            ->count();
    }

    public function markAsRead()
    {
        $this->messages()
            ->where('direction', 'inbound')
            ->where('status', '!=', 'read')
            ->update(['status' => 'read', 'read_at' => now()]);
    }
}