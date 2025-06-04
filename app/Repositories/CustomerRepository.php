<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\Customer;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CustomerRepository extends AppRepository
{
    protected $model;

    public function __construct(Customer $model)
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

        if ($request->has('email_search') && isset($request->email_search)) {
            $model->where('email', $request->email_search)->first();
        }
        // if ($request->filled('email_search')) {
        //     $query = $model->where('email', $request->email_search);
        //     //dd($query->toSql(), $query->getBindings()); // Debug SQL query
        //     return $query->first();
        // }

        if ($request->has('keyword') && isset($request->keyword)) {
            $model->whereLike(['name'], $request->keyword);
        }

        if ($request->has('oldest') && isset($request->oldest)) {
            $model->oldest();
        } else {
            $model->latest();
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
        if ($request->has('title') && isset($request->title)) {
            $model->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->has('fax') && isset($request->fax)) {
            $model->where('fax', 'like', '%' . $request->fax . '%');
        }
        if ($request->has('company_name') && isset($request->company_name)) {
            $model->where('company_name', 'like', '%' . $request->company_name . '%');
        }
        if ($request->has('full_name') && isset($request->full_name)) {
            $model->where('full_name', 'like', '%' . $request->full_name . '%');
        }
        if ($request->has('email') && isset($request->email)) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('phone_number') && isset($request->phone_number)) {
            $model->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }
        if ($request->has('alternate_phone') && isset($request->alternate_phone)) {
            $model->where('alternate_phone', 'like', '%' . $request->alternate_phone . '%');
        }

        if ($request->has('address') && isset($request->address)) {
            $model->where('address', 'like', '%' . $request->address . '%');
        }

        if ($request->has('gender') && isset($request->gender)) {
            $model->where('gender', 'like', '%' . $request->gender . '%');
        }
        if ($request->has('dob') && isset($request->dob)) {
            $model->where('dob', 'like', '%' . $request->dob . '%');
        }
        if ($request->has('postal_code') && isset($request->postal_code)) {
            $model->where('postal_code', 'like', '%' . $request->postal_code . '%');
        }

        if ($request->has('languages') && isset($request->languages)) {
            $model->where('languages', 'like', '%' . $request->languages . '%');
        }
        if ($request->has('medical_information') && isset($request->medical_information)) {
            $model->where('medical_information', 'like', '%' . $request->medical_information . '%');
        }
        if ($request->has('passport_number') && isset($request->passport_number)) {
            $model->where('passport_number', 'like', '%' . $request->passport_number . '%');
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
