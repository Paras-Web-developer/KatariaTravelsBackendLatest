<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldAirport extends Model
{
    // Specify the table name if it's not the plural form of the model name
    protected $table = 'world_airports';

    // Specify the fillable fields for mass assignment
    protected $fillable = [
        'icao',
        'iata',
        'name',
        'city',
        'state',
        'country',
        'elevation',
        'lat',
        'lon',
        'tz',
    ];

    // Enable timestamps (created_at, updated_at)
    public $timestamps = true;

    // Define casts for proper data types
    protected $casts = [
        'elevation' => 'integer',
        'lat' => 'float',
        'lon' => 'float',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
