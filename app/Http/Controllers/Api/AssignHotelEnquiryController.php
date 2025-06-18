<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\HotelEnquireRepository;
use App\Http\Resources\HotelEnquireResource;
use App\Http\Requests\AssignedHotelEnquiryRequest;

use App\Models\Enquiry;
use App\Models\MultiPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Events\EmployeeEvent;
use App\Events\EmployeeMultiCityEvent;
use App\Models\HotelEnquire;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignHotelEnquiryController extends BaseController
{
    protected $hotelEnquireRepo;

    public function __construct(HotelEnquireRepository $hotelEnquireRepo)
    {
        $this->hotelEnquireRepo = $hotelEnquireRepo;
    }
    public function assignedHotelEnquiryList(Request $request)
    {
        $user = auth()->user();
        $limit = $request->has('limit') ? $request->limit : 1000;

        $query = $this->hotelEnquireRepo->filter()
            ->with(
                'createdByUser',
                'updatedByUser',
                'transactionType',
                'parent',
                'children',
                'enquirySource',
                'enquiryStatus',
                'assignedToUser',
                'children.createdByUser',
                'children.updatedByUser',
                'children.transactionType',
                'children.parent',
                'children.enquirySource',
                'children.enquiryStatus',
                'children.assignedToUser',
            )->whereNull('parent_id');

        $query->where('assigned_to_user_id', $user->id);

        $response = $query->latest()->paginate($limit);
        $enquiriesWithPending = $response->map(function ($enquiry) {
            $enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
            return $enquiry;
        });
        return $this->successWithPaginateData(HotelEnquireResource::collection($enquiriesWithPending), $response);
    }
    public function saveAndUpdateAssignedHotelEnquiry(AssignedHotelEnquiryRequest $request)
    {

        // Validation for enquiry fields
        $user = auth()->user();

        $enquiryPaymentStatus = 'not_paid';
        if ($request->paid_amount > 0) {
            if ($request->paid_amount < $request->budget) {
                $enquiryPaymentStatus = 'pending';
            } elseif ($request->paid_amount == $request->budget) {
                $enquiryPaymentStatus = 'paid';
            } elseif ($request->paid_amount > $request->budget) {
                $enquiryPaymentStatus = 'over_paid';
            }
        }

        if ($request->id) {
            $parentEnquiry = HotelEnquire::find($request->id);

            if (!$parentEnquiry) {
                return response()->json([
                    'status' => false,
                    'message' => "Hotel Enquiry with ID {$request->id} not found"
                ], 404);
            }
            $parentEnquiry->update([
                'updated_by_user_id' => $user->id,
                'transaction_type_id' => $request->transaction_type_id ? $request->transaction_type_id : $parentEnquiry->transaction_type_id,
                'title' => $request->title ? $request->title : $parentEnquiry->title,
                'budget' => $request->budget ? $request->budget : $parentEnquiry->budget,
                'enquiry_source_id' => $request->enquiry_source_id ? $request->enquiry_source_id : $parentEnquiry->enquiry_source_id,
                'assigned_to_user_id' => $request->assigned_to_user_id ? $request->assigned_to_user_id :    $parentEnquiry->assigned_to_user_id,
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'enquiry_status_id' => $request->enquiry_status_id ? $request->enquiry_status_id : $parentEnquiry->enquiry_status_id,
                'full_name' => $request->full_name ? $request->full_name : $parentEnquiry->full_name,
                'phone_number' => $request->phone_number ? $request->phone_number : $parentEnquiry->phone_number,
                'email' => $request->email ? $request->email : $parentEnquiry->email,
                'invoice_number' => $request->invoice_number ? $request->invoice_number : $parentEnquiry->invoice_number,
                'paid_amount' => $request->paid_amount ? $request->paid_amount : $parentEnquiry->paid_amount,
                'status' => $request->status ? $request->status : $parentEnquiry->status,
                'destination' => $request->destination ? $request->destination : $parentEnquiry->destination,
                'check_in_date' => $request->check_in_date ? $request->check_in_date : $parentEnquiry->check_in_date,
                'check_out_date' => $request->check_out_date ? $request->check_out_date : $parentEnquiry->check_out_date,
                'guest' => $request->guest ? $request->guest : $parentEnquiry->guest,
                'room' => $request->room ? $request->room : $parentEnquiry->room,
                'special_requests' => $request->special_requests ? $request->special_requests : $parentEnquiry->special_requests,
                'booking_reference' => $request->booking_reference ? $request->booking_reference : $parentEnquiry->booking_reference
            ]);
        }

        // Create or update the enquiry

        if ($request->id) {
            $enquiry = HotelEnquire::Create(
                [
                    'transaction_type_id' => $request->transaction_type_id,
                    'updated_by_user_id' => $user->id,
                    'parent_id' => $request->id,
                    'created_by_user_id' => $user->id,
                    'title' => $request->title,
                    'full_name' => $request->full_name,
                    'budget' => $request->budget,
                    'enquiry_source_id' => $request->enquiry_source_id,
                    'enquiry_payment_status' => $enquiryPaymentStatus,
                    'assigned_to_user_id' => $user->id,
                    'enquiry_status_id' => $request->enquiry_status_id,
                    'customer_name' => $request->customer_name ? $request->customer_name : $parentEnquiry->customer_name,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                    'destination' => $request->destination,
                    'booking_reference' => $request->booking_reference,
                    'invoice_number' => $request->invoice_number,
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'guest' => $request->guest,
                    'room' => $request->room,
                    'special_requests' => $request->special_requests,
                    //'remark' => $request->remark,
                    'paid_amount' => $request->paid_amount,
                    'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $request->id),
                    'status' => $request->status ? $request->status : $parentEnquiry->status,
                ]
            );
        }
        ///EmployeeEvent::dispatch($enquiry);

        // Return success message based on whether it is a create or update
        if ($request->id) {
            return $this->success(new HotelEnquireResource($enquiry), 'Hotel Enquiry updated successfully');
        } else {
            return $this->success(new HotelEnquireResource($enquiry), 'Hotel Enquiry created successfully');
        }
    }
}
