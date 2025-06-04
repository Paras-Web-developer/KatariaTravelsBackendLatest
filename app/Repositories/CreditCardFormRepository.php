<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\CreditCardForm;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CreditCardFormRepository extends AppRepository
{
    protected $model;

    public function __construct(CreditCardForm $model)
    {
        $this->model = $model;
    }

    public function storeData(array $data)
    {

        return CreditCardForm::create($data);
    }
    public function updateData($id, array $data)
    {
        $creditCardForm = CreditCardForm::findOrFail($id);
        $creditCardForm->update($data);
        return $creditCardForm;
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
        if ($request->has('airLine_id') && isset($request->airLine_id)) {
            $model->where('airLine_id', $request->airLine_id);
        }
        if ($request->has('holder_name') && isset($request->holder_name)) {
            $model->where('holder_name', 'like', '%' . $request->holder_name . '%');
        }

        if ($request->has('type') && isset($request->type)) {
            $model->where('type', $request->type);
        }

        if ($request->has('card_number') && isset($request->card_number)) {
            $model->where('card_number', 'like', '%' . $request->card_number . '%');
        }
        if ($request->has('expire_date') && isset($request->expire_date)) {
            $model->where('expire_date', $request->expire_date);
        }

        if ($request->has('cvv') && isset($request->cvv)) {
            $model->where('cvv', $request->cvv);
        }

        if ($request->has('amount') && isset($request->amount)) {
            $model->where('amount', $request->amount);
        }

        if ($request->has('travel_date') && isset($request->travel_date)) {
            $model->where('travel_date', $request->travel_date);
        }

        if ($request->has('transportation') && isset($request->transportation)) {
            $model->where('transportation', $request->transportation);
        }

        if ($request->has('country') && isset($request->country)) {
            $model->where('country', $request->country);
        }

        if ($request->has('state') && isset($request->state)) {
            $model->where('state', $request->state);
        }

        if ($request->has('city') && isset($request->city)) {
            $model->where('city', $request->city);
        }

        if ($request->has('postal_code') && isset($request->postal_code)) {
            $model->where('postal_code', $request->postal_code);
        }

        if ($request->has('phone_number') && isset($request->phone_number)) {
            $model->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        return $model;
    }
}
