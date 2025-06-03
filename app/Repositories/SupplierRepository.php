<?php

namespace App\Repositories;

use App\Models\EnquiryStatus;
use App\Models\Supplier;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class SupplierRepository extends AppRepository
{
    protected $model;

    public function __construct(Supplier $model)
    {
        $this->model = $model;
    }
    public function with($relations)
    {
        return $this->model->with($relations);
    }


    public function filter()
    {
        $request = request();
        $model = $this->query();

        if ($request->has('keyword') && isset($request->keyword)) {
            $model->whereLike(['name', 'slug'], $request->keyword);
        }

        if ($request->has('oldest') && isset($request->oldest)) {
            $model->oldest();
        } else {
            $model->latest();
        }

        if ($request->has('name') && isset($request->name)) {
            $model->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('slug') && isset($request->slug)) {
            $model->where('slug', 'like', '%' . $request->slug . '%');
        }
        if ($request->has('type') && isset($request->type)) {
            $model->where('type', 'like', '%' . $request->type . '%');
        }

        if ($request->has('supplier_code') && isset($request->supplier_code)) {
            $model->where('supplier_code', 'like', '%' . $request->supplier_code . '%');
        }

        if ($request->has('reservations_phone') && isset($request->reservations_phone)) {
            $model->where('reservations_phone', 'like', '%' . $request->reservations_phone . '%');
        }
        if ($request->has('reservations_email') && isset($request->reservations_email)) {
            $model->where('reservations_email', 'like', '%' . $request->reservations_email . '%');
        }
        if ($request->has('contact_name') && isset($request->contact_name)) {
            $model->where('contact_name', 'like', '%' . $request->contact_name . '%');
        }
        if ($request->has('phone') && isset($request->phone)) {
            $model->where('phone', 'like', '%' . $request->phone . '%');
        }
       
        if ($request->has('fax') && isset($request->fax)) {
            $model->where('fax', 'like', '%' . $request->fax . '%');
        }

        if ($request->has('email') && isset($request->email)) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('country_id') && isset($request->country_id)) {
            $model->where('country_id', $request->country_id);
        }
        if ($request->has('state_id') && isset($request->state_id)) {
            $model->where('state_id', $request->state_id);
        }
        if ($request->has('city_id') && isset($request->city_id)) {
            $model->where('city_id', $request->city_id);
        }
        if ($request->has('postal_code') && isset($request->postal_code)) {
            $model->where('postal_code', 'like', '%' . $request->postal_code . '%');
        }
        if ($request->has('address') && isset($request->address)) {
            $model->where('address', 'like', '%' . $request->address . '%');
        }
      
        if ($request->has('start_date') && $request->has('end_date')) {
            $model->whereBetween('created_at', [
                $request->start_date,
                date('Y-m-d 23:59:59', strtotime($request->end_date))
            ]);
        } elseif ($request->has('start_date')) {
            $model->where('created_at', '>=', $request->start_date);
        } elseif ($request->has('end_date')) {
            $model->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->end_date)));
        }
        
        if ($request->has('created_at_date') && isset($request->created_at_date)) {
            $model->whereDate('created_at', '=', $request->created_at_date);
        }
      
        if ($request->has('updated_at_date') && isset($request->updated_at_date)) {
            $model->whereDate('updated_at', '=', $request->updated_at_date);
        }
           
        return $model;
    }
}
