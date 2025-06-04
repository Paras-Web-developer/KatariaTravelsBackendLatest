<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'title',
        'fax',
        'company_name',
        'full_name',
        'email',
        'phone_number',
        'alternate_phone',
        'address',
        'state',
        'city',
        'postal_code',
        'dob',
        'gender',
        'languages',
        'medical_information',
        'passport_number'
    ];

    public function countryName(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
  
    public function stateName(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }
  
    public function cityName(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class, 'airLine_id');
    }

    public function invoiceMains(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'customer_id');
    }
}
