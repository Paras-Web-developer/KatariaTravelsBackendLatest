<?php

namespace App\Repositories;

use App\Models\EnquiryStatus;
use App\Models\PhoneNumber;
use App\Models\Supplier;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PhoneNumberRepository extends AppRepository
{
    protected $model;

    public function __construct(PhoneNumber $model)
    {
        $this->model = $model;
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
            $model->where('name', $request->name);
        }
        if ($request->has('slug') && isset($request->slug)) {
            $model->where('slug', $request->slug);
        }
        return $model;
    }
}
