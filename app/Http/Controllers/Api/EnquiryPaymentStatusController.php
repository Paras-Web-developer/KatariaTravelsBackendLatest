<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\EnquiryPaymentStatusRepository;
use App\Http\Resources\EnquiryPaymentStatusResource;
use App\Models\EnquiryPaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EnquiryPaymentStatusController extends BaseController
{
    protected $enquiryPaymentStatusRepo;

    public function __construct(EnquiryPaymentStatusRepository $enquiryPaymentStatusRepo)
    {
        $this->enquiryPaymentStatusRepo = $enquiryPaymentStatusRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->enquiryPaymentStatusRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(EnquiryPaymentStatusResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        
        $request->validate([
            'id' => 'nullable|integer|exists:enquiry_payment_statuses,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('enquiry_payment_statuses', 'slug')->ignore($request->id),
            ],
        ]);

        
        $response = $this->enquiryPaymentStatusRepo->update($request->id, $request);

       
        if ($request->id) {
            return $this->success(new EnquiryPaymentStatusResource($response), 'enquiry payment Updated Successfully');
        } else {
            return $this->success(new EnquiryPaymentStatusResource($response), 'enquiry payment statuses Created Successfully');
        }

        
    }

      public function delete($id){
      
        $enquiryPaymentStatus = EnquiryPaymentStatus::find($id);

        if(!$enquiryPaymentStatus){
            return response()->json([
            'message' => $message ?? 'Enquiry Payment Status record not found',
        ], 500);
        }
        
        $enquiryPaymentStatus->delete();
       
        return $this->success(['flash_type' => 'success', 'flash_message' => 'Enquiry Payment Status deleted successfully', 'flash_description' => $enquiryPaymentStatus->name]);
    }
}

