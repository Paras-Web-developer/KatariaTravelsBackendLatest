<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerInvoice extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'airLine_id',
        'customer_id',
        'invoice_number',
        'booking_date',
        'departure_date',
        'sales_agent',
        'travel_from',
        'travel_to',
        'gds_locator',
        'gds_type',
        'itinerary',
    ];

    public function airLine(): BelongsTo
    {
        return $this->belongsTo(AirLine::class, 'airLine_id');
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function flightDetails(): HasMany
    {
        return $this->hasMany(FlightDetails::class, 'customer_invoice_id');
    }
    public function passengerDetails(): HasMany
    {
        return $this->hasMany(PassengerDetails::class, 'customer_invoice_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(PaymentDetail::class, 'customer_invoice_id');
    }
    public function insuranceDetails(): HasMany
    {
        return $this->hasMany(InsuranceDetail::class, 'customer_invoice_id');
    }

    public function hotelCruiseLandPackages(): HasMany
    {
        return $this->hasMany(HotelCruiseLandPackage::class, 'customer_invoice_id');
    }
    

}
