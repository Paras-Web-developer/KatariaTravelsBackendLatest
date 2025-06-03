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

class CarEnquiry extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enquiry_status_id',
        'created_by_user_id',
        'enquiry_source_id',
        'car_type_id',
        'enquiry_type',
        'pick_up_location',
        'drop_off_location',
        'pick_up_date',
        'drop_off_date',
        'title',
        'full_name',
        'email',
        'phone_number',
        'special_requests',
        'parent_id',
        'updated_by_user_id',
        'assigned_to_user_id',
        'enquiry_payment_status_id',
        'budget',
        'invoice_number',
        'paid_amount',
        'status',
    ];

    public function enquiryStatus() :BelongsTo {
        return $this->belongsTo(EnquiryStatus::class , 'enquiry_status_id');
    }

    function parent(): BelongsTo
    {
        return $this->belongsTo(CarEnquiry::class, 'parent_id');
    }

    function children(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'parent_id');
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
    public function carType(): BelongsTo
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }
}
