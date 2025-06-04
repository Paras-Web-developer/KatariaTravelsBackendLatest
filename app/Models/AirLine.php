<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AirLine extends Model
{
    use HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'airline_name',
        'slug',
        'price',
        'airline_code',
        'country',
    ];


    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'air_line_id');
    }
    public function CreditCardForm(): HasMany
    {
        return $this->hasMany(CreditCardForm::class, 'airLine_id');
    }
    public function FlightDetails(): HasMany
    {
        return $this->hasMany(FlightDetails::class, 'airLine_id');
    }
    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class, 'airLine_id');
    }

    public function invoiceMains(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'airLine_id');
    }
}
