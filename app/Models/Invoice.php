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
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    use HasFactory;

    // protected $fillable = [
    //     'agent_user_id',
    //     'transaction_type_id',
    //     'supplier_id',
    //     'invoice_number',
    //     'enquiry_id',
    //     'date',
    //     'pnr',
    //     'cost',
    //     'sold_fare',
    //     'amex_card',
    //     'cibc_card',
    //     'td_busness_visa_card',
    //     'bmo_master_card',
    //     'rajni_mam',
    //     'td_fc_visa',
    //     'ch_eq_ue',
    //     'ticket_number',
    //     'fnu',
    //     'airLine_id',
    //     'ticket_status',
    //     'reference_number_of_et',
    //     'remarks',
    // ];

    protected $fillable = [
        'parent_id',
        'agent_user_id',
        'transaction_type_id',
        'transaction_type_agency_id',
        'supplier_id',
        'enquiry_id',
        'invoice_number',
        'invoice_holder_name',
        'tickets',
        'date',
        'temp_supplier',
        'pnr',
        'ch_eq_ue',
        'airLine_id',
        'ticket_status',
        'reference_number_of_et',
        'remarks',
        'agency_payment',
];
    protected $casts = [
        'tickets' => 'array',  // Laravel will automatically serialize to JSON on save, deserialize on retrieve
    ];

    function parent(): BelongsTo
	{
		return $this->belongsTo(Invoice::class, 'parent_id');
	}
    // function invoiceMains(): HasMany
	// {
	// 	return $this->hasMany(InvoiceMain::class, 'invoice_id');
	// }
    public function invoiceMain(): HasOne
        {
            return $this->hasOne(InvoiceMain::class, 'invoice_id');
        }

	function children(): HasMany
	{
		return $this->hasMany(Invoice::class, 'parent_id');
	}


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function agentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }
     public function transactionTypeAgency(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_agency_id');
    }
    


    // Define the relationship with the AirLine model (if necessary)
    public function airLine()
    {
        return $this->belongsTo(AirLine::class, 'airLine_id');
    }
    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class, 'enquiry_id');
    }
}
