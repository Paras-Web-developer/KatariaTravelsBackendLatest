<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\CarEnquiry;
use App\Models\HotelEnquire;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CarEnquireRepository extends AppRepository
{
    protected $model;

    public function __construct(CarEnquiry $model)
    {
        $this->model = $model;
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


        if ($request->has('enquiry_source_id') && isset($request->enquiry_source_id)) {
            $model->where('enquiry_source_id', $request->enquiry_source_id);
        }
        if ($request->has('car_type_id') && isset($request->car_type_id)) {
            $model->where('car_type_id', $request->car_type_id);
        }
        if ($request->has('created_by_user_id') && isset($request->created_by_user_id)) {
            $model->where('created_by_user_id', $request->created_by_user_id);
        }

        if ($request->has('title') && isset($request->title)) {
            $model->where('title', 'like', '%' . $request->title . '%');
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
        if ($request->has('enquiry_type') && isset($request->enquiry_type)) {
            $model->where('enquiry_type', $request->enquiry_type);
        }
        if ($request->has('pick_up_location') && isset($request->pick_up_location)) {
            $model->where('pick_up_location', 'like', '%' . $request->pick_up_location . '%');
        }
        if ($request->has('drop_off_location') && isset($request->drop_off_location)) {
            $model->where('drop_off_location', 'like', '%' . $request->drop_off_location . '%');
        }
        if ($request->has('pick_up_date') && isset($request->pick_up_date)) {
            $model->where('pick_up_date', 'like', '%' . $request->pick_up_date . '%');
        }
        if ($request->has('drop_off_date') && isset($request->drop_off_date)) {
            $model->where('drop_off_date', 'like', '%' . $request->drop_off_date . '%');
        }

        if ($request->has('status') && isset($request->status)) {
            $model->where('status', $request->status);
        }
        if ($request->has('updated_by_user_id') && isset($request->updated_by_user_id)) {
            $model->where('updated_by_user_id', $request->updated_by_user_id);
        }
        if ($request->has('assigned_to_user_id') && isset($request->assigned_to_user_id)) {
            $model->where('assigned_to_user_id', $request->assigned_to_user_id);
        }
        if ($request->has('enquiry_payment_status_id') && isset($request->enquiry_payment_status_id)) {
            $model->where('enquiry_payment_status_id', $request->enquiry_payment_status_id);
        }
        if ($request->has('min_price') && $request->has('max_price')) {
            // If both min_price and max_price are provided, filter within the range
            $model->whereBetween('budget', [$request->min_price, $request->max_price]);
        } else {
            // If only min_price is provided, filter records with budget >= min_price
            if ($request->has('min_price')) {
                $model->where('budget', '>=', $request->min_price);
            }

            // If only max_price is provided, filter records with budget <= max_price
            if ($request->has('max_price')) {
                $model->where('budget', '<=', $request->max_price);
            }
        }
        if ($request->has('invoice_number') && isset($request->invoice_number)) {
            $model->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
         if ($request->has('enquiry_status_id') && isset($request->enquiry_status_id)) {
            $model->where('enquiry_status_id', $request->enquiry_status_id);
        }
        if ($request->has('paid_amount') && isset($request->paid_amount)) {
            $model->where('paid_amount', 'like', '%' . $request->paid_amount . '%');
        }


        return $model;
    }
}
