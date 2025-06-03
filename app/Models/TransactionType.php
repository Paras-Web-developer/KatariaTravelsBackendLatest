<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'transaction_type_id');
    }

    public function hotelEnquiries(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'transaction_type_id');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'transaction_type_id');
    }

    public function otherServices(): HasMany
    {
        return $this->hasMany(OtherService::class, 'transaction_type_id');
    }

}
