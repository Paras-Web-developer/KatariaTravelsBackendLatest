<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\CustomerInvoice;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CustomerInvoiceRepository extends AppRepository
{
    protected $model;

    public function __construct(CustomerInvoice $model)
    {
        $this->model = $model;
    }

    public function saveOrUpdate($data)
    {
        if (!empty($data['id'])) {

            $customerInvoice = CustomerInvoice::find($data['id']);
            if (!$customerInvoice) {
                return null; // Handle not found case
            }
            $customerInvoice->update($data);
        } else {
            $customerInvoice = CustomerInvoice::create($data);
        }

        return $customerInvoice;
    }


    public function filter()
    {
        $request = request();
        $model = $this->query();

        if ($request->has('keyword') && isset($request->keyword)) {
            $model->whereLike(['name'], $request->keyword);
        }

        if ($request->has('oldest') && isset($request->oldest)) {
            $model->oldest();
        } else {
            $model->latest();
        }
        if ($request->has('airline_name') && isset($request->airline_name)) {
            $model->where('airline_name', 'like', '%' . $request->airline_name . '%');
        }
        if ($request->has('airline_code') && isset($request->airline_code)) {
            $model->where('airline_code', 'like', '%' . $request->airline_code . '%');
        }
        if ($request->has('slug') && isset($request->slug)) {
            $model->where('slug', 'like', '%' . $request->slug . '%');
        }

        return $model;
    }
}
