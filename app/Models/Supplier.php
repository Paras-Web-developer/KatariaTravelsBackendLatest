<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Supplier extends Authenticatable
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
        'name',
        'type',
        'supplier_code',
        'reservations_phone',
        'reservations_email',
        'contact_name',
        'phone',
        'fax',
        'email',
        'address',
        'postal_code',
        'slug',
        'status'
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
  

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'supplier_id');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_id');
    }
    public function invoiceMains(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'supplier_id');
    }
}
