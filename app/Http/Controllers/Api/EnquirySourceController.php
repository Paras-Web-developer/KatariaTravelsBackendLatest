<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\EnquirySourceRepository;
use App\Http\Resources\EnquirySourceResource;
use App\Models\EnquirySource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EnquirySourceController extends BaseController
{
    protected $enquirySourceRepo;

    public function __construct(EnquirySourceRepository $enquirySourceRepo)
    {
        $this->enquirySourceRepo = $enquirySourceRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->enquirySourceRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(EnquirySourceResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        
        $request->validate([
            'id' => 'nullable|integer|exists:enquiry_sources,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('enquiry_sources', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->enquirySourceRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new EnquirySourceResource($response), 'enquiry Source Updated Successfully');
        } else {
            return $this->success(new EnquirySourceResource($response), 'enquiry Source Created Successfully');
        }
    }

     public function delete($id){
      
        $enquirySource = EnquirySource::find($id);

        if(!$enquirySource){
            return response()->json([
            'message' => $message ?? 'Enquiry Source record not found',
        ], 500);
        }
        
        $enquirySource->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Enquiry Source deleted successfully', 'flash_description' => $enquirySource->name]);
    }
}
