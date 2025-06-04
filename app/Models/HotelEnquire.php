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
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelEnquire extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'enquiry_code',
		'transaction_type_id',
		'enquiry_payment_status_id',
		'enquiry_payment_status',
		'enquiry_status_id',
		'created_by_user_id',
		'enquiry_source_id',
		'enquiry_type',
		'title',
		'full_name',
		'email',
		'phone_number',
		'destination',
		'check_in_date',
		'check_out_date',
		'guest',
		'room',
		'special_requests',
		'parent_id',
		'updated_by_user_id',
		'assigned_to_user_id',
		'budget',
		'invoice_number',
		'paid_amount',
		'status',
		'booking_reference',
		'admin_payment_status',
		'note',
		'follow_up_message',
		'follow_up_at',
	];

	public function transactionType(): BelongsTo
	{
		return $this->belongsTo(TransactionType::class, 'transaction_type_id');
	}


	public function enquiryStatus(): BelongsTo
	{
		return $this->belongsTo(EnquiryStatus::class, 'enquiry_status_id');
	}
	function parent(): BelongsTo
	{
		return $this->belongsTo(HotelEnquire::class, 'parent_id');
	}

	function children(): HasMany
	{
		return $this->hasMany(HotelEnquire::class, 'parent_id');
	}
	public function updatedByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by_user_id');
	}

	public function assignedToUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'assigned_to_user_id');
	}
	public function enquiryPaymentStatus(): BelongsTo
	{
		return $this->belongsTo(EnquiryPaymentStatus::class, 'enquiry_payment_status_id');
	}


	public function createdByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by_user_id');
	}

	public function enquirySource(): BelongsTo
	{
		return $this->belongsTo(EnquirySource::class, 'enquiry_source_id');
	}
	public function invoiceMains(): HasMany
	{
		return $this->hasMany(InvoiceMain::class, 'hotel_enquire_id');
	}

	public function followupMessages(): HasMany
	{
		return $this->morphMany(FollowupMessage::class, 'followupable');
	}
}
