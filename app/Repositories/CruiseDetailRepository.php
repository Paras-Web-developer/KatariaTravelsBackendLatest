<?php

namespace App\Repositories;

use App\Models\CruiseDetail;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CruiseDetailRepository extends AppRepository
{
    protected $model;

    public function __construct(CruiseDetail $model)
    {
        $this->model = $model;
    }
  
    public function saveOrUpdate($data, $customerInvoiceId)
    {
        $data['customer_invoice_id'] = $customerInvoiceId;
        
        if (!empty($data['id'])) {
            $cruiseDetail = CruiseDetail::find($data['id']);
            if ($cruiseDetail) {
                $cruiseDetail->update($data);
            }
        } else {
            $cruiseDetail = CruiseDetail::create($data);
        }
        return $cruiseDetail;
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
