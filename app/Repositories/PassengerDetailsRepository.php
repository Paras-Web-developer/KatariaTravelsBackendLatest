<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\FlightDetails;
use App\Models\PassengerDetails;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PassengerDetailsRepository extends AppRepository
{
    protected $model;

    public function __construct(PassengerDetails $model)
    {
        $this->model = $model;
    }
    public function saveOrUpdate($data, $customerInvoiceId)
    {
        $data['customer_invoice_id'] = $customerInvoiceId;
        if (!empty($data['id'])) {
            $passengerDetails = PassengerDetails::find($data['id']);
            if ($passengerDetails) {
                $passengerDetails->update($data);
            }else{
                $passengerDetails = PassengerDetails::create($data);
            }
        } else {
            $passengerDetails = PassengerDetails::create($data);
        }
        //dd($passengerDetails);
        return $passengerDetails;
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
        if ($request->has('first_name') && isset($request->first_name)) {
            $model->where('first_name', 'like', '%' . $request->first_name . '%');
        }
        if ($request->has('last_name') && isset($request->last_name)) {
            $model->where('last_name', $request->last_name);
        }
        if ($request->has('title') && isset($request->title)) {
            $model->where('title', $request->title);
        }
        if ($request->has('bill_to') && isset($request->bill_to)) {
            $model->where('bill_to', $request->bill_to);
        }
        if ($request->has('add_insurance') && isset($request->add_insurance)) {
            $model->where('add_insurance', $request->add_insurance);
        }

        return $model;
    }
}
