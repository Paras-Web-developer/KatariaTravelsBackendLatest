<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\Enquiry;
use App\Models\FlightBookingEnquiry;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class FlightBookingEnquiryRepository extends AppRepository
{
    protected $model;

    public function __construct(FlightBookingEnquiry $model)
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
        if ($request->has('full_name') && isset($request->full_name)) {
            $model->where('full_name', 'like', '%' . $request->full_name . '%');
        }
        if ($request->has('email') && isset($request->email)) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('phone') && isset($request->phone)) {
            $model->where('phone', $request->phone);
        }
        if ($request->has('message') && isset($request->message)) {
            $model->where('message', $request->message);
        }
        if ($request->has('departure_city') && isset($request->departure_city)) {
            $model->where('departure_city', $request->departure_city);
        }
        if ($request->has('destination_city') && isset($request->destination_city)) {
            $model->where('destination_city', $request->destination_city);
        }
        if ($request->has('travel_date') && isset($request->travel_date)) {
            $model->where('travel_date', $request->travel_date);
        }
        if ($request->has('return_date') && isset($request->return_date)) {
            $model->where('return_date', $request->return_date);
        }
        if ($request->has('no_of_passengers') && isset($request->no_of_passengers)) {
            $model->where('no_of_passengers', $request->no_of_passengers);
        }

        return $model;
    }
}
