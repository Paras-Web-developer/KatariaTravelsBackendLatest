<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\RoleRepository;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    protected $roleRepo;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    public function list(Request $request)
    {
       
        $limit = $request->has('limit') ? $request->limit : 1000;
        $response = $this->roleRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(RoleResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
       
        $request->validate([
            'id' => 'nullable|integer|exists:roles,id',
            'name' => ['string', 'max:255'],
            'slug' => 'required|string|unique:roles,slug',
            'discount' => ['numeric', 'between:0,5'], 
            'description' => 'nullable|string'
        ]);
        $response = $this->roleRepo->update($request->id, $request);
        return $this->success(new RoleResource($response), 'create the Role');
    }
     public function delete($id){
      
        $role = Role::find($id);

        if(!$role){
            return response()->json([
            'message' => $message ?? 'Role record not found',
        ], 500);
        }
        $role->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Role deleted successfully', 'flash_description' => $role->title]);
    }

   
}
