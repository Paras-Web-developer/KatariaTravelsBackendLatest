<?php

namespace App\Repositories;

use App\Models\LandPackageDetail;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class LandPackageDetailRepository extends AppRepository
{
    protected $model;

    public function __construct(LandPackageDetail $model)
    {
        $this->model = $model;
    }
  
    public function saveOrUpdate($data, $customerInvoiceId)
    {
        dd($data);
        $data['customer_invoice_id'] = $customerInvoiceId;
        
        if (!empty($data['id'])) {
            $landPackageDetail = LandPackageDetail::find($data['id']);
            if ($landPackageDetail) {
                $landPackageDetail->update($data);
            }
        } else {
            $landPackageDetail = LandPackageDetail::create($data);
        }
        return $landPackageDetail;
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
