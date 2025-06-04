<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'country_id');
    }
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'country_id');
    }
    
    public function hotelCruiseLandPackages(): HasMany
    {
        return $this->hasMany(HotelCruiseLandPackage::class, 'country_id');
    }
    
}
