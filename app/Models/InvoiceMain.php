<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;


class InvoiceMain extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;
	protected $table = 'invoices_mains';

	protected $fillable = [
		'airLine_id',
		'customer_id',
		'supplier_id',
		'invoice_number',
		'sales_agent',
		'itinerary',
		'gds_type',
		'gds_locator',
		'booking_date',
		'departure_date',
		'ticket_number',
		'mco',
		'status',
		'travel_from',
		'travel_to',
		'cruise',
		'hotel',
		'insurance',
		'land_package',
		'misc',
		'pdf_path',
		'valid_canadian_passport',
		'valid_travel_visa',
		'airticket',
		'customer_details',
		'tourist_card',
		'canadian_citizenship_or_prCard',
		'special_remarks',
		'other_remarks',
		'hotel_enquire_id',
		'flight_enquiry_id',
		'sales_agent_id',
		'parent_id',
		'airticket_include',
		'insurance_include',
		'misc_include',
		'land_package_include',
		'hotel_include',
		'cruise_include',
		'created_by_user_id',
		'updated_by_user_id'
	];

	protected $casts = [
		'airticket' => 'array',
		'insurance' => 'array',
		'misc' => 'array',
		'land_package' => 'array',
		'hotel' => 'array',
		'cruise' => 'array',
		'customer_details' => 'array',
		'passenger_details' => 'array'
	];

	public function updatedByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by_user_id');
	}

	public function createdByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by_user_id');
	}

	function parent(): BelongsTo
	{
		return $this->belongsTo(InvoiceMain::class, 'parent_id');
	}

	function children(): HasMany
	{
		return $this->hasMany(InvoiceMain::class, 'parent_id');
	}


	public function sales_agents(): BelongsTo
	{
		return $this->belongsTo(User::class, 'sales_agent_id');
	}

	public function airLine(): BelongsTo
	{
		return $this->belongsTo(AirLine::class, 'airLine_id');
	}
	public function customer(): BelongsTo
	{
		return $this->belongsTo(Customer::class, 'customer_id');
	}
	public function supplier(): BelongsTo
	{
		return $this->belongsTo(Supplier::class, 'supplier_id');
	}

	public function hotelEnquiry(): BelongsTo
	{
		return $this->belongsTo(HotelEnquire::class, 'hotel_enquire_id');
	}
	public function flightEnquiry(): BelongsTo
	{
		return $this->belongsTo(Enquiry::class, 'flight_enquiry_id');
	}

	public function fromAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'travel_from');
	}

	public function toAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'travel_to');
	}
}
