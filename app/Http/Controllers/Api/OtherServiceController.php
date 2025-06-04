<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\OtherServiceRequest;
use App\Http\Requests\AcceptRejectServiceRequest;
use App\Repositories\OtherServiceRepository;
use App\Http\Resources\OtherServiceResource;
use App\Models\AirLine;
use App\Models\OtherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OtherServiceController extends BaseController
{
    protected $otherServiceRepo;

    public function __construct(OtherServiceRepository $otherServiceRepo)
    {
        $this->otherServiceRepo = $otherServiceRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->otherServiceRepo->filter()->with(
            'enquirySource',
            'enquiryStatus',
            'assignedUser',
            'transactionType',
            'parent',
            'children',
            'children.createdByUser',
            'children.updatedByUser',
            'children.enquirySource',
            'children.enquiryStatus',
            'children.assignedUser',
            'children.transactionType',
            'createdByUser',
            'updatedByUser',
        )->where('parent_id', null)->latest()->paginate($limit);
        return $this->successWithPaginateData(OtherServiceResource::collection($response), $response);
    }
    public function saveAndUpdate(OtherServiceRequest $otherServiceRequest)
    {

        $enquiryPaymentStatus = 'not_paid';
        if ($otherServiceRequest->paid_amount > 0) {
            if ($otherServiceRequest->paid_amount < $otherServiceRequest->price_quote) {
                $enquiryPaymentStatus = 'pending';
            } elseif ($otherServiceRequest->paid_amount == $otherServiceRequest->price_quote) {
                $enquiryPaymentStatus = 'paid';
            } elseif ($otherServiceRequest->paid_amount > $otherServiceRequest->price_quote) {
                $enquiryPaymentStatus = 'over_paid';
            }
        }

        if ($otherServiceRequest->id) {
            $OtherServiceEnquiry = OtherService::find($otherServiceRequest->id);

            $adminPaymentStatus = $OtherServiceEnquiry->admin_payment_status;
            if ($otherServiceRequest->paid_amount > $OtherServiceEnquiry->paid_amount || $otherServiceRequest->paid_amount < $OtherServiceEnquiry->paid_amount) {
                $adminPaymentStatus = 'pending';
            }
        } else {
            $adminPaymentStatus = 'no_action';
            if ($otherServiceRequest->paid_amount > 0) {
                $adminPaymentStatus = 'pending';
            }
        }


        if ($otherServiceRequest->id != null) {
            $otherServiceRequest->merge([
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'updated_by_user_id' => auth()->user()->id
            ]);
            $parentRecord = $this->otherServiceRepo->update($otherServiceRequest->id, $otherServiceRequest);

            $otherServiceRequest->merge([
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $otherServiceRequest->id),
                'parent_id' => $otherServiceRequest->id,
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'created_by_user_id' => auth()->user()->id,
                'updated_by_user_id' => auth()->user()->id,

            ]);
            $ChildResponse = $this->otherServiceRepo->store($otherServiceRequest);
        } else {
            $otherServiceRequest->merge([
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(),
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'created_by_user_id' => auth()->user()->id,
                'updated_by_user_id' => auth()->user()->id,

            ]);
            $response = $this->otherServiceRepo->update($otherServiceRequest->id, $otherServiceRequest);
            //dd($response->id);
            if ($response->id != null) {
                $otherServiceRequest->merge([
                    'parent_id' => $response->id,
                    'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $response->id),
                    'enquiry_payment_status' => $enquiryPaymentStatus,
                    'admin_payment_status' => $adminPaymentStatus,
                    'created_by_user_id' => auth()->user()->id,
                    'updated_by_user_id' => auth()->user()->id,
                ]);
                $ChildResponse = $this->otherServiceRepo->store($otherServiceRequest);
            }
        }

        if ($otherServiceRequest->id) {
            return $this->success(new OtherServiceResource($ChildResponse), 'Other Service Enquiry Updated Successfully');
        } else {
            return $this->success(new OtherServiceResource($response), 'Other Service Enquiry Created Successfully');
        }
    }

    public function acceptOrReject(AcceptRejectServiceRequest $request, $id)
    {
        $userId = auth()->user();
        try {
            $otherServiceEnquiry = OtherService::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Other Service Enquiry with ID {$id} not found",
            ], 404);
        }

        if ($request->status == 'reject' && $otherServiceEnquiry->assigned_to_user_id == $userId->id) {
            $otherServiceEnquiry->assigned_to_user_id = null;
            $otherServiceEnquiry->status = $request->status;
            $otherServiceEnquiry->save();

            $childOtherServiceEnquiry = OtherService::Create([
                'created_by_user_id' => $userId->id,
                'updated_by_user_id' => $userId->id,
                'parent_id' => $otherServiceEnquiry->id,
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $otherServiceEnquiry->id),
                'enquiry_source_id' => $otherServiceEnquiry->enquiry_source_id,
                'enquiry_status_id' => $otherServiceEnquiry->enquiry_status_id,
                'assigned_to_user_id' => $otherServiceEnquiry->assigned_to_user_id,
                'title' => $otherServiceEnquiry->title,
                'customer_name' => $otherServiceEnquiry->customer_name,
                'email' => $otherServiceEnquiry->email,
                'phone_number' => $otherServiceEnquiry->phone_number,
                'price_quote' => $otherServiceEnquiry->price_quote,
                'paid_amount' => $otherServiceEnquiry->paid_amount,
                'invoice_number' => $otherServiceEnquiry->invoice_number,
                'booking_reference_no' => $otherServiceEnquiry->booking_reference_no,
                'special_requests' => $otherServiceEnquiry->special_requests,
                'service_name' => $otherServiceEnquiry->service_name,
                'status' => $request->status,
                'enquiry_payment_status' => $otherServiceEnquiry->enquiry_payment_status,
                'admin_payment_status' => $otherServiceEnquiry->admin_payment_status,
                'note' => $otherServiceEnquiry->note,
            ]);

            return $this->success([
                'flash_type' => 'success',
                'data' => new OtherServiceResource($childOtherServiceEnquiry),
                'flash_message' => 'Other Service Enquiry status set to reject successfully',
                'flash_description' => $otherServiceEnquiry->title
            ]);
        } else {
            $otherServiceEnquiry->status = $request->status;
            $otherServiceEnquiry->assigned_to_user_id = $userId->id;
            $otherServiceEnquiry->save();
            $childOtherServiceEnquiry = OtherService::Create([
                'created_by_user_id' => $userId->id,
                'updated_by_user_id' => $userId->id,
                'parent_id' => $otherServiceEnquiry->id,
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $otherServiceEnquiry->id),
                'enquiry_source_id' => $otherServiceEnquiry->enquiry_source_id,
                'enquiry_status_id' => $otherServiceEnquiry->enquiry_status_id,
                'assigned_to_user_id' => $otherServiceEnquiry->assigned_to_user_id,
                'title' => $otherServiceEnquiry->title,
                'customer_name' => $otherServiceEnquiry->customer_name,
                'email' => $otherServiceEnquiry->email,
                'phone_number' => $otherServiceEnquiry->phone_number,
                'price_quote' => $otherServiceEnquiry->price_quote,
                'paid_amount' => $otherServiceEnquiry->paid_amount,
                'invoice_number' => $otherServiceEnquiry->invoice_number,
                'booking_reference_no' => $otherServiceEnquiry->booking_reference_no,
                'special_requests' => $otherServiceEnquiry->special_requests,
                'service_name' => $otherServiceEnquiry->service_name,
                'status' => $request->status,
                'enquiry_payment_status' => $otherServiceEnquiry->enquiry_payment_status,
                'admin_payment_status' => $otherServiceEnquiry->admin_payment_status,
                'note' => $otherServiceEnquiry->note,
            ]);

            return $this->success([
                'flash_type' => 'success',
                'data' => new OtherServiceResource($childOtherServiceEnquiry),
                'flash_message' => 'Other Service Enquiry status updated successfully',
                'flash_description' => $otherServiceEnquiry->title
            ]);
        }
    }

    public function delete($id)
    {

        $otherService = OtherService::find($id);

        if (!$otherService) {
            return response()->json([
                'message' => $message ?? 'other Service record not found',
            ], 500);
        }

        $otherService->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'otherServices deleted successfully', 'flash_description' => $otherService->title]);
    }

    public function acceptOrRejectByAdmin(Request $request, $id)
    {

        $user = auth()->user();
        $request->validate([
            'admin_payment_status' => 'required|string|in:approved,pending,no_action',
            'note' => 'nullable|string',
        ]);


        //$hotelEnquiry = HotelEnquire::findOrFail($id);
        $otherServiceEnquiry = $this->otherServiceRepo->update($id, $request);
        //dd($otherServiceEnquiry);
        if ($otherServiceEnquiry->id) {
            $request->merge([
                'created_by_user_id' => $user->id,
                'updated_by_user_id' => $user->id,
                'parent_id' => $otherServiceEnquiry->id,
                'enquiry_source_id' => $otherServiceEnquiry->enquiry_source_id,
                'enquiry_status_id' => $otherServiceEnquiry->enquiry_status_id,
                'assigned_to_user_id' => $otherServiceEnquiry->assigned_to_user_id,
                'title' => $otherServiceEnquiry->title,
                'customer_name' => $otherServiceEnquiry->customer_name,
                'email' => $otherServiceEnquiry->email,
                'phone_number' => $otherServiceEnquiry->phone_number,
                'price_quote' => $otherServiceEnquiry->price_quote,
                'paid_amount' => $otherServiceEnquiry->paid_amount,
                'invoice_number' => $otherServiceEnquiry->invoice_number,
                'booking_reference_no' => $otherServiceEnquiry->booking_reference_no,
                'special_requests' => $otherServiceEnquiry->special_requests,
                'service_name' => $otherServiceEnquiry->service_name,
                'status' => $otherServiceEnquiry->status,
                'enquiry_payment_status' => $otherServiceEnquiry->enquiry_payment_status,
                'admin_payment_status' => $request->admin_payment_status,
                'note' => $otherServiceEnquiry->note,
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $otherServiceEnquiry->id),
            ]);
            $ChildResponse = $this->otherServiceRepo->store($request);
            return response()->json([
                'status' => true,
                'message' => 'Other Service Enquiry status updated successfully.',
                'otherServiceEnquiry' => new OtherServiceResource($ChildResponse),
            ]);
        }
    }
}
