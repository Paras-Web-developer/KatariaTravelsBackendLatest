<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Role;
use App\Models\Messages;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class InvoiceRepository extends AppRepository
{
    protected $model;

    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }


    // public function filter()
    // {
    //     $request = request();
    //     $model = $this->query();

    //     if ($request->has('keyword') && isset($request->keyword)) {
    //         $model->whereLike(['name', 'slug'], $request->keyword);
    //     }
    //     if ($request->has('supplier_id') && isset($request->supplier_id)) {
    //         $model->where('supplier_id', $request->supplier_id);
    //     }

    //     if ($request->has('oldest') && isset($request->oldest)) {
    //         $model->oldest();
    //     } else {
    //         $model->latest();
    //     }

    //     if ($request->has('name') && isset($request->name)) {
    //         $model->where('name', $request->name);
    //     }
    //     if ($request->has('enquiry_id') && isset($request->enquiry_id)) {
    //         $model->where('enquiry_id', $request->enquiry_id);
    //     }

    //     if ($request->has('status') && isset($request->status)) {
    //         $model->where('status', $request->status);
    //     }
    //     return $model;
    // }

    public function filter()
    {
        $request = request();
        $model = $this->query();

        if ($request->filled('invoice_number')) {
            $model->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('invoice_holder_name')) {
            $model->where('invoice_holder_name', 'like', '%' . $request->invoice_holder_name . '%');
        }

        if ($request->filled('transaction_type_id')) {
            $model->where('transaction_type_id', $request->transaction_type_id);
        }

        if ($request->filled('transaction_type_agency_id')) {
            $model->where('transaction_type_agency_id', $request->transaction_type_agency_id);
        }

        if ($request->filled('agent_user_id')) {
            $model->where('agent_user_id', $request->agent_user_id);
        }

        if ($request->filled('supplier_id')) {
            $model->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('enquiry_id')) {
            $model->where('enquiry_id', $request->enquiry_id);
        }

        if ($request->filled('airLine_id')) {
            $model->where('airLine_id', $request->airLine_id);
        }

        if ($request->filled('pnr')) {
            $model->where('pnr', 'like', '%' . $request->pnr . '%');
        }

        if ($request->filled('temp_supplier')) {
            $model->where('temp_supplier', 'like', '%' . $request->temp_supplier . '%');
        }

        if ($request->filled('ticket_status')) {
            $model->where('ticket_status', $request->ticket_status);
        }

        if ($request->filled('reference_number_of_et')) {
            $model->where('reference_number_of_et', 'like', '%' . $request->reference_number_of_et . '%');
        }

        if ($request->filled('remarks')) {
            $model->where('remarks', 'like', '%' . $request->remarks . '%');
        }

        if ($request->filled('date')) {
            $model->whereDate('date', $request->date);
        }

        // Sorting
        if ($request->boolean('oldest')) {
            $model->oldest();
        } else {
            $model->latest();
        }

        return $model;
    }
}
