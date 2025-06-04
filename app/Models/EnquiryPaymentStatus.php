<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnquiryPaymentStatus extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'enquiry_payment_status_id');
    }
    public function hotelEnquiries(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'enquiry_payment_status_id');
    }
    public function carEnquiries(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'enquiry_payment_status_id');
    }
}
