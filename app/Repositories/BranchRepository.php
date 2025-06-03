<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class BranchRepository extends AppRepository
{
    protected $model;

    public function __construct(Branch $model)
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
        
        return $model;
    }
}
