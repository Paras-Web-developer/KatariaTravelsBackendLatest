<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\FlightDetails;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class FlightDetailsRepository extends AppRepository
{
    protected $model;

    public function __construct(FlightDetails $model)
    {
        $this->model = $model;
    }

    public function saveOrUpdate($data, $customerInvoiceId)
    {
        $data['customer_invoice_id'] = $customerInvoiceId;

        if (!empty($data['id'])) {
            $flightDetails = FlightDetails::find($data['id']);
            if ($flightDetails) {
                $flightDetails->update($data);
            }else{
                $flightDetails = FlightDetails::create($data);
            }
        } else {
            $flightDetails = FlightDetails::create($data);
        }

        return $flightDetails;
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
        if ($request->has('flight_no') && isset($request->flight_no)) {
            $model->where('flight_no', 'like', '%' . $request->flight_no . '%');
        }
        if ($request->has('airLine_id') && isset($request->airLine_id)) {
            $model->where('airLine_id', $request->airLine_id);
        }
        if ($request->has('from') && isset($request->from)) {
            $model->where('from', $request->from);
        }
        if ($request->has('to') && isset($request->to)) {
            $model->where('to', $request->to);
        }
        if ($request->has('date') && isset($request->date)) {
            $model->where('date', $request->date);
        }
        if ($request->has('dep_time') && isset($request->dep_time)) {
            $model->where('dep_time', $request->dep_time);
        }
        return $model;
    }
}
