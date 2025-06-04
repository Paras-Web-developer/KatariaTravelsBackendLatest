<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use  HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'department_id',
        'branch_id',
        'age',
        'salary',
        'name',
        'email',
        'phone_no',
        'is_verified',
        'employee_verified_at',
        'image',
        'description',
        'password',
        'status',
        'user_login',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }




    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url('storage/' . $this->image);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'assigned_to_user_id');
    }
    public function hotelEnquiries(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'assigned_to_user_id');
    }
    public function carEnquiries(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'assigned_to_user_id');
    }
    public function enquiriesUpdatedBy(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'updated_by_user_id');
    }
    public function hotelEnquiriesUpdatedBy(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'updated_by_user_id');
    }
    public function carEnquiriesUpdatedBy(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'updated_by_user_id');
    }

    public function otherServicesCreatedBy(): HasMany
    {
        return $this->hasMany(OtherService::class, 'created_by_user_id');
    }

    public function otherServicesUpdatedBy(): HasMany
    {
        return $this->hasMany(OtherService::class, 'updated_by_user_id');
    }


    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_user_id');
    }

    public function enquiriesCreatedBy(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'created_by_user_id');
    }
    public function carEnquiriesCreatedBy(): HasMany
    {
        return $this->hasMany(CarEnquiry::class, 'created_by_user_id');
    }
    public function hotelEnquiriesCreatedBy(): HasMany
    {
        return $this->hasMany(HotelEnquire::class, 'created_by_user_id');
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'agent_user_id');
    }
    public function receiveUserChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }
    public function senderUserChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function OtherServices(): HasMany
    {
        return $this->hasMany(OtherService::class, 'assigned_to_user_id');
    }
    public function invoiceMains(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'sales_agent_id');
    }

    public function invoiceCreatedBy(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'created_by_user_id');
    }
    public function invoiceUpdatedBy(): HasMany
    {
        return $this->hasMany(InvoiceMain::class, 'updated_by_user_id');
    }
}
