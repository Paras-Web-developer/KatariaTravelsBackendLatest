<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use App\Models\InvoiceMain;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'enquiry_code',
		'parent_id',
		'supplier_id',
		'transaction_type_id',
		'created_by_user_id',
		'assigned_to_user_id',
		'updated_by_user_id',
		'enquiry_source_id',
		'enquiry_payment_status',
		'enquiry_payment_status_id',
		'air_line_id',
		'enquiry_status_id',
		'enquiry_type',
		'customer_name',
		'title',
		'phone_number',
		'email',
		'package_type',
		'departure_date',
		'return_date',
		'from',
		'to',
		'class_of_travel',
		'adult',
		'child',
		'infant',
		'type',
		'booking_reference',
		'budget',
		'invoice_number',
		'remark',
		'paid_amount',
		'status',
		'followed_up_at',
		'follow_up_message',
		'admin_payment_status',
		'note',
	];

	public function supplier(): BelongsTo
	{
		return $this->belongsTo(Supplier::class, 'supplier_id');
	}

	public function transactionType(): BelongsTo
	{
		return $this->belongsTo(TransactionType::class, 'transaction_type_id');
	}

	function parent(): BelongsTo
	{
		return $this->belongsTo(Enquiry::class, 'parent_id');
	}

	function children(): HasMany
	{
		return $this->hasMany(Enquiry::class, 'parent_id');
	}

	public function updatedByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by_user_id');
	}

	public function createdByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by_user_id');
	}
	public function assignedToUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'assigned_to_user_id');
	}
	public function airLine(): BelongsTo
	{
		return $this->belongsTo(AirLine::class, 'air_line_id');
	}
	public function enquiryStatus(): BelongsTo
	{
		return $this->belongsTo(EnquiryStatus::class, 'enquiry_status_id');
	}
	public function enquirySource(): BelongsTo
	{
		return $this->belongsTo(EnquirySource::class, 'enquiry_source_id');
	}
	// public function enquiryPaymentStatus() :BelongsTo {
	//     return $this->belongsTo(EnquiryPaymentStatus::class , 'enquiry_payment_status_id');
	// }
	public function packages(): HasMany
	{
		return $this->hasMany(Package::class, 'enquiry_id');
	}

	public function invoices(): HasMany
	{
		return $this->hasMany(Invoice::class, 'enquiry_id');
	}
	public function invoiceMains(): HasMany
	{
		return $this->hasMany(InvoiceMain::class, 'flight_enquiry_id');
	}

	public function followupMessages(): HasMany
	{
		return $this->morphMany(FollowupMessage::class, 'followupable');
	}

	public function fromAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'from');
	}

	public function toAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'to');
	}
}
