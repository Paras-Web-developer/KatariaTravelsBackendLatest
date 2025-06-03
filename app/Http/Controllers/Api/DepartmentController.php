<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\DepartmentRepository;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class DepartmentController extends BaseController
{
    protected $departmentRepo;

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepo = $departmentRepo;
    }

    public function list(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->departmentRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(DepartmentResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        // Validate the request
        $request->validate([
            'id' => 'nullable|integer|exists:departments,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('departments', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->departmentRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new DepartmentResource($response), 'Department Updated Successfully');
        } else {
            return $this->success(new DepartmentResource($response), 'Department Created Successfully');
        }
    }

     public function delete($id){
      
        $department = Department::find($id);

        if(!$department){
            return response()->json([
            'message' => $message ?? 'department record not found',
        ], 500);
        }
        
        $department->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'department deleted successfully', 'flash_description' => $department->name]);
    }

}
