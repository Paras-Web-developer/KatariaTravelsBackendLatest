<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\CarTypeRepository;
use App\Http\Resources\CarTypeResource;
use App\Models\CarType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class CarTypeController extends BaseController
{
    protected $carTypeRepo;

    public function __construct(CarTypeRepository $carTypeRepo)
    {
        $this->carTypeRepo = $carTypeRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 1000;
        $response = $this->carTypeRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(CarTypeResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        
        $request->validate([
            'id' => 'nullable|integer|exists:car_types,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('car_types', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->carTypeRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new CarTypeResource($response), 'Car TypeUpdated Successfully');
        } else {
            return $this->success(new CarTypeResource($response), 'Car Type statuses Created Successfully');
        }
    }

    public function delete($id){
      
        $carType = CarType::find($id);

        if(!$carType){
            return response()->json([
            'message' => $message ?? 'car Type record not found',
        ], 500);
        }
        
        $carType->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Car Type deleted successfully', 'flash_description' => $carType->name]);
    }
}

