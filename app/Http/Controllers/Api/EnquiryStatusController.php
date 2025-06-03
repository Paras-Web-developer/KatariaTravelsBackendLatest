<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\EnquiryStatusRepository;
use App\Http\Resources\EnquiryStatusResource;
use App\Models\EnquiryStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EnquiryStatusController extends BaseController
{
    protected $enquiryStatusRepo;

    public function __construct(EnquiryStatusRepository $enquiryStatusRepo)
    {
        $this->enquiryStatusRepo = $enquiryStatusRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->enquiryStatusRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(EnquiryStatusResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        
        $request->validate([
            'id' => 'nullable|integer|exists:enquiry_statuses,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('enquiry_statuses', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->enquiryStatusRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new EnquiryStatusResource($response), 'enquiry statuses Updated Successfully');
        } else {
            return $this->success(new EnquiryStatusResource($response), 'enquiry statuses Created Successfully');
        }
    }

    public function delete($id){
      
        $enquiryStatus = EnquiryStatus::find($id);

        if(!$enquiryStatus){
            return response()->json([
            'message' => $message ?? 'Enquiry status record not found',
        ], 500);
        }
        
        $enquiryStatus->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Enquiry status deleted successfully', 'flash_description' => $enquiryStatus->name]);
    }
}
