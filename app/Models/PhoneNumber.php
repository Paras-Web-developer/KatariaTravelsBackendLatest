<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneNumber extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enquiry_id',
        'hotel_enquiry_id',
        'phone_number',
        'message_id',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'message_phone_numbers', 'message_id');
    }
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
