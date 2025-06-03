<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repositories\BranchRepository;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class BranchController extends BaseController
{
    protected $branchRepo;

    public function __construct(BranchRepository $branchRepo)
    {
        $this->branchRepo = $branchRepo;
    }

    public function list(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->branchRepo->filter()->with('users')->latest()->paginate($limit);
        return $this->successWithPaginateData(BranchResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        // Validate the request
        $request->validate([
            'id' => 'nullable|integer|exists:branches,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('branches', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->branchRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new BranchResource($response), 'Branch Updated Successfully');
        } else {
            return $this->success(new BranchResource($response), 'Branch Created Successfully');
        }
    }

     public function delete($id){
      
        $branch = Branch::find($id);

        if(!$branch){
            return response()->json([
            'message' => $message ?? 'branch record not found',
        ], 500);
        }
        
        $branch->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'branch deleted successfully', 'flash_description' => $branch->name]);
    }

}

