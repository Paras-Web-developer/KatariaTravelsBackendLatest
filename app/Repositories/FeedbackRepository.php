<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\Feedback;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class FeedbackRepository extends AppRepository
{
    protected $model;

    public function __construct(Feedback $model)
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
        if ($request->has('email') && isset($request->email)) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('phone_number') && isset($request->phone_number)) {
            $model->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }
        if ($request->has('rating') && isset($request->rating)) {
            $model->where('rating', 'like', '%' . $request->rating . '%');
        }


        return $model;
    }
}
