<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnquiryStatus extends Authenticatable
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
        return $this->hasMany(Enquiry::class, 'enquiry_status_id');
    }
    public function hotelEnquires(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'enquiry_status_id');
    }
    public function carEnquires(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'enquiry_status_id');
    }

    public function OtherServices(): HasMany
    {
        return $this->hasMany(OtherService::class, 'enquiry_status_id');
    }
}
