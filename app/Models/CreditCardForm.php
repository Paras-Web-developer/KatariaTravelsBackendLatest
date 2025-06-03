<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardForm extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'airLine_id',
        'holder_name',
        'type',
        'card_number',
        'expire_date',
        'cvv',
        'amount',
        'travel_date',
        'transportation',
        'country',
        'state',
        'city',
        'postal_code',
        'phone_number',
        'signature'
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url('storage/' . $this->signature);
    }


    public function airLine()
    {
        return $this->belongsTo(AirLine::class, 'airLine_id');
    }
}
