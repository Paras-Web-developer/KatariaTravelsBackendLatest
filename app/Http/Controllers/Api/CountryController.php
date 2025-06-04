<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Country;
use App\Models\State;
use App\Models\City;


class CountryController extends BaseController
{
    public function countryList()
    {
        $countries = Country::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $countries
        ]);
    }
    public function stateList($countryId)
    {
        $states = State::where('country_id', $countryId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $states
        ]);
    }
    public function cityList($countryId, $stateId)
    {
        $cities = City::where('country_id',$countryId)->where('state_id', $stateId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $cities
        ]);
    }
}


