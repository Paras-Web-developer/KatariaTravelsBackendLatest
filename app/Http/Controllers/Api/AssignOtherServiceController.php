<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\OtherServiceRepository;
use App\Http\Resources\OtherServiceResource;
use App\Http\Requests\AssignOtherServiceEnquiryRequest;

use Illuminate\Http\Request;
use App\Models\HotelEnquire;
use App\Models\OtherService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignOtherServiceController extends BaseController
{
    protected $otherServiceRepo;

    public function __construct(OtherServiceRepository $otherServiceRepo)
    {
        $this->otherServiceRepo = $otherServiceRepo;
    }
    public function assignedOtherEnquiryList(Request $request)
    {
        $user = auth()->user();
        $limit = $request->has('limit') ? $request->limit : 1000;

        $query = $this->otherServiceRepo->filter()
            ->with(
                'enquirySource',
                'enquiryStatus',
                'assignedUser',
                'transactionType',
                'parent',
                'children',
                'createdByUser',
                'updatedByUser',
                'children.createdByUser',
                'children.updatedByUser',
                'children.enquirySource',
                'children.enquiryStatus',
                'children.assignedUser',
                'children.transactionType',
            )->whereNull('parent_id');

        $query->where('assigned_to_user_id', $user->id);

        $response = $query->latest()->paginate($limit);
        $enquiriesWithPending = $response->map(function ($enquiry) {
            $enquiry->pendingAmount = max(0, $enquiry->price_quote - $enquiry->paid_amount);
            return $enquiry;
        });
        return $this->successWithPaginateData(OtherServiceResource::collection($enquiriesWithPending), $response);
    }
    public function saveAndUpdateAssignedOtherEnquiry(AssignOtherServiceEnquiryRequest $request)
    {
        $user = auth()->user();
        $enquiryPaymentStatus = 'not_paid';
        if ($request->paid_amount > 0) {
            if ($request->paid_amount < $request->price_quote) {
                $enquiryPaymentStatus = 'pending';
            } elseif ($request->paid_amount == $request->price_quote) {
                $enquiryPaymentStatus = 'paid';
            } elseif ($request->paid_amount > $request->price_quote) {
                $enquiryPaymentStatus = 'over_paid';
            }
        }

        if ($request->id) {
            $OtherServiceEnquiry = OtherService::find($request->id);

            $adminPaymentStatus = $OtherServiceEnquiry->admin_payment_status;
            if ($request->paid_amount > $OtherServiceEnquiry->paid_amount || $request->paid_amount < $OtherServiceEnquiry->paid_amount) {
                $adminPaymentStatus = 'pending';
            }
        } else {
            $adminPaymentStatus = 'no_action';
            if ($request->paid_amount > 0) {
                $adminPaymentStatus = 'pending';
            }
        }


        if ($request->id != null) {
            $request->merge([
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'updated_by_user_id' => auth()->user()->id
            ]);
            $parentRecord = $this->otherServiceRepo->update($request->id, $request);

            $request->merge([
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $request->id),
                'parent_id' => $request->id,
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'created_by_user_id' => auth()->user()->id,
            ]);
            $ChildResponse = $this->otherServiceRepo->store($request);
        } else {
            $request->merge([
                'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(),
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'admin_payment_status' => $adminPaymentStatus,
                'created_by_user_id' => auth()->user()->id,
            ]);
            $response = $this->otherServiceRepo->update($request->id, $request);
            //dd($response->id);
            if ($response->id != null) {
                $request->merge([
                    'parent_id' => $response->id,
                    'enquiry_code' => $this->otherServiceRepo->generateEnquiryCode(true, $response->id),
                    'enquiry_payment_status' => $enquiryPaymentStatus,
                    'admin_payment_status' => $adminPaymentStatus,
                    'created_by_user_id' => auth()->user()->id,
                ]);
                $ChildResponse = $this->otherServiceRepo->store($request);
            }
        }


        return $this->success(new OtherServiceResource($parentRecord), 'Other Service Enquiry updated successfully');
    }
}
