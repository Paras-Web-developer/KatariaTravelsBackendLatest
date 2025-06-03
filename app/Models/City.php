<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'city_id');
    }
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'city_id');
    }
}
