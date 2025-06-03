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


    public function filter()
    {
        $request = request();
        $model = $this->query();

        if ($request->has('keyword') && isset($request->keyword)) {
            $model->whereLike(['name', 'slug'], $request->keyword);
        }
        if ($request->has('supplier_id') && isset($request->supplier_id)) {
            $model->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('oldest') && isset($request->oldest)) {
            $model->oldest();
        } else {
            $model->latest();
        }

        if ($request->has('name') && isset($request->name)) {
            $model->where('name', $request->name);
        }
        if ($request->has('enquiry_id') && isset($request->enquiry_id)) {
            $model->where('enquiry_id', $request->enquiry_id);
        }

        if ($request->has('status') && isset($request->status)) {
            $model->where('status', $request->status);
        }
        return $model;
    }
}
