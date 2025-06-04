<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\PackageRepository;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends BaseController
{
    protected $packageRepo;

    public function __construct(PackageRepository $packageRepo)
    {
        $this->packageRepo = $packageRepo;
    }

    public function list(Request $request)
    {
       
        $limit = $request->has('limit') ? $request->limit : 10;
        $response = $this->packageRepo->filter()->with('users')->latest()->paginate($limit);
        return $this->successWithPaginateData(PackageResource::collection($response), $response);
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
        $response = $this->packageRepo->update($request->id, $request);
        return $this->success(new PackageResource($response), 'package');
    }

    public function delete($id){
      
        $package = Package::find($id);

        if(!$package){
            return response()->json([
            'message' => $message ?? 'Package record not found',
        ], 500);
        }
        $package->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Package deleted successfully', 'flash_description' => $package->title]);
    }
}

