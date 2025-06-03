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

class OtherService extends Authenticatable
{
	use HasApiTokens, HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'transaction_type_id',
		'created_by_user_id',
		'updated_by_user_id',
		'parent_id',
		'enquiry_code',
		'enquiry_source_id',
		'enquiry_status_id',
		'assigned_to_user_id',
		'title',
		'customer_name',
		'email',
		'phone_number',
		'price_quote',
		'paid_amount',
		'invoice_number',
		'booking_reference_no',
		'special_requests',
		'service_name',
		'status',
		'enquiry_payment_status',
		'admin_payment_status',
	];


	public function transactionType(): BelongsTo
	{
		return $this->belongsTo(TransactionType::class, 'transaction_type_id');
	}

	public function createdByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by_user_id');
	}

	public function updatedByUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by_user_id');
	}


	public function parent(): BelongsTo
	{
		return $this->belongsTo(OtherService::class, 'parent_id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(OtherService::class, 'parent_id');
	}
	public function enquirySource(): BelongsTo
	{
		return $this->belongsTo(EnquirySource::class, 'enquiry_source_id');
	}

	public function enquiryStatus(): BelongsTo
	{
		return $this->belongsTo(EnquiryStatus::class, 'enquiry_status_id');
	}

	public function assignedUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'assigned_to_user_id');
	}

	public function followupMessages(): HasMany
	{
		return $this->morphMany(FollowupMessage::class, 'followupable');
	}
}
