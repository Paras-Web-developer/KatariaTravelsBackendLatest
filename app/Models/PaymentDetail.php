<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentDetail extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_type_of_payment',
        'customer_invoice_id',
        'payment_details_for',
        'adult',
        'child',
        'inf',
        'a_base_fare',
        'a_gst',
        'a_taxes',
        'a_sub_total',
        'c_base_fare',
        'c_gst',
        'c_taxes',
        'c_sub_total',
        'i_base_fare',
        'i_gst',
        'i_taxes',
        'i_sub_total',
        'total_amount',
        'paid_amount',
        'balance',
        'payment_method_type',
        'commission',
        'payment_recieved_from_pax',
        'payment_made_to_supplier',
        'commission_recieved_from_supplier'
    ];

    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
    }

}
