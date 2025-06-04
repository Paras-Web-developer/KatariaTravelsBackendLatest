<?php

namespace App\Repositories;

use App\Models\WorldAirport;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class WorldAirportRepository extends AppRepository
{
    protected $model;

    public function __construct(WorldAirport $model)
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
        if ($request->has('icao') && isset($request->icao)) {
            $model->where('icao', 'like', '%' . $request->icao . '%');
        }
        if ($request->has('iata') && isset($request->iata)) {
            $model->where('iata', 'like', '%' . $request->iata . '%');
        }
        if ($request->has('name') && isset($request->name)) {
            $model->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('city') && isset($request->city)) {
            $model->where('city', 'like', '%' . $request->city . '%');
        }
        if ($request->has('state') && isset($request->state)) {
            $model->where('state', 'like', '%' . $request->state . '%');
        }
        if ($request->has('country') && isset($request->country)) {
            $model->where('country', 'like', '%' . $request->country . '%');
        }
        if ($request->has('elevation') && isset($request->elevation)) {
            $model->where('elevation', 'like', '%' . $request->elevation . '%');
        }
        if ($request->has('lat') && isset($request->lat)) {
            $model->where('lat', 'like', '%' . $request->lat . '%');
        }
        if ($request->has('lon') && isset($request->lon)) {
            $model->where('lon', 'like', '%' . $request->lon . '%');
        }
        if ($request->has('tz') && isset($request->tz)) {
            $model->where('tz', 'like', '%' . $request->tz . '%');
        }

        return $model;
    }
}
