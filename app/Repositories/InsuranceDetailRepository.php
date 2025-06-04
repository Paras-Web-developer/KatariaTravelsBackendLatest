<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\InsuranceDetail;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class InsuranceDetailRepository extends AppRepository
{
    protected $model;

    public function __construct(InsuranceDetail $model)
    {
        $this->model = $model;
    }

    public function saveOrUpdate($data, $customerInvoiceId)
    {
        $data['customer_invoice_id'] = $customerInvoiceId;
        
        if (!empty($data['id'])) {
            $insuranceDetail = InsuranceDetail::find($data['id']);
            if ($insuranceDetail) {
                $insuranceDetail->update($data);
            }else{
                $insuranceDetail = InsuranceDetail::create($data);
            }
        } else {
            $insuranceDetail = InsuranceDetail::create($data);
        }
        return $insuranceDetail;
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
        if ($request->has('customer_invoice_id') && isset($request->customer_invoice_id)) {
            $model->where('customer_invoice_id', 'like', '%' . $request->customer_invoice_id . '%');
        }
        if ($request->has('first_name') && isset($request->first_name)) {
            $model->where('first_name', 'like', '%' . $request->first_name . '%');
        }
        if ($request->has('last_name') && isset($request->last_name)) {
            $model->where('last_name', 'like', '%' . $request->last_name . '%');
        }


        return $model;
    }
}
