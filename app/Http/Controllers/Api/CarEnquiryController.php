<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\CarEnquireRepository;
use App\Http\Resources\CarEnquireResource;
use App\Models\CarEnquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\EmployeeCarEnquiryEvent;


class CarEnquiryController extends BaseController
{
    protected $carEnquireRepo;

    public function __construct(CarEnquireRepository $carEnquireRepo)
    {
        $this->carEnquireRepo = $carEnquireRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 5;
        $response = $this->carEnquireRepo->filter()->with(
            'carType',
            'updatedByUser',
            'parent',
            'children',
            'createdByUser',
            'enquirySource',
            'enquiryStatus',
            'assignedToUser',
            'children.updatedByUser',
            'children.createdByUser',
            'children.enquirySource',
            'children.assignedToUser',
            'children.enquiryStatus'
        )->whereNull('parent_id')->latest()->paginate($limit);
        return $this->successWithPaginateData(CarEnquireResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        // Validate the request

        $user = auth()->user();

        $request->validate([
            'id' => 'nullable|integer|exists:car_enquiries,id',
            'parent_id' => 'nullable|integer|exists:car_enquiries,id',
            'enquiry_status_id' => 'required|integer|exists:enquiry_statuses,id',
            'enquiry_payment_status_id' => 'required|integer|exists:enquiry_payment_statuses,id',
            'assigned_to_user_id' => 'required|integer|exists:users,id',
            'budget' => ['required', 'numeric'],
            'invoice_number' => 'nullable|string',
            'paid_amount' => ['required', 'numeric'],
            'status' => 'nullable|string|in:pending,accept,reject',
            'enquiry_source_id' => 'nullable|integer|exists:enquiry_sources,id',
            'car_type_id' => 'nullable|integer|exists:car_types,id',
            'pick_up_date' => 'required|date',
            'drop_off_date' => 'required|date|after_or_equal:check_in_date',
            'pick_up_location' => 'nullable|string',
            'drop_off_location' => 'nullable|string',
            'title' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|max:15',
            'special_requests' => 'nullable|string|max:500',
        ]);
        if ($request->id && $request->assigned_to_user_id) {
            $parentEnquiry = CarEnquiry::find($request->id);
            $parentEnquiry->enquiry_status_id = $request->enquiry_status_id;
            $parentEnquiry->updated_by_user_id = $user->id;
            $parentEnquiry->assigned_to_user_id = $request->assigned_to_user_id;
            $parentEnquiry->enquiry_payment_status_id = $request->enquiry_payment_status_id;
            $parentEnquiry->budget = $request->budget;
            $parentEnquiry->invoice_number = $request->invoice_number;
            $parentEnquiry->paid_amount = $request->paid_amount;
            $parentEnquiry->status = $request->status ? $request->status : $parentEnquiry->status;
            $parentEnquiry->title = $request->title;
            $parentEnquiry->enquiry_source_id = $request->enquiry_source_id;
            $parentEnquiry->car_type_id = $request->car_type_id;
            $parentEnquiry->pick_up_date = $request->pick_up_date;
            $parentEnquiry->drop_off_date = $request->drop_off_date;
            $parentEnquiry->pick_up_location = $request->pick_up_location;
            $parentEnquiry->drop_off_location = $request->drop_off_location;
            // $parentEnquiry->check_in_date = $request->check_in_date;
            //$parentEnquiry->check_out_date = $request->check_out_date;
            $parentEnquiry->full_name = $request->full_name;
            $parentEnquiry->email = $request->email;
            $parentEnquiry->phone_number = $request->phone_number;
            $parentEnquiry->special_requests = $request->special_requests;
            $parentEnquiry->save();
        }

        if ($request->id) {
            $carEnquiry = CarEnquiry::updateOrCreate(
                [
                    'updated_by_user_id' => $user->id,
                    'enquiry_status_id' => $request->enquiry_status_id,
                    'parent_id' => $request->id,
                    'assigned_to_user_id' => $request->assigned_to_user_id,
                    'enquiry_payment_status_id' => $request->enquiry_payment_status_id,
                    'budget' => $request->budget,
                    'invoice_number' => $request->invoice_number,
                    'paid_amount' => $request->paid_amount,
                    'status' => $request->status ? $request->status : $parentEnquiry->status,
                    'title' => $request->title,
                    'enquiry_source_id' => $request->enquiry_source_id,
                    'car_type_id' => $request->car_type_id,
                    'pick_up_date' => $request->pick_up_date,
                    'drop_off_date' => $request->drop_off_date,
                    'pick_up_location' => $request->pick_up_location,
                    'drop_off_location' => $request->drop_off_location,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'special_requests' => $request->special_requests,
                ]
            );
        } else {
            $carEnquiry = CarEnquiry::Create(
                [
                    'enquiry_status_id' => $request->enquiry_status_id,
                    'created_by_user_id' => $user->id,
                    'assigned_to_user_id' => $request->assigned_to_user_id,
                    'enquiry_payment_status_id' => $request->enquiry_payment_status_id,
                    'budget' => $request->budget,
                    'invoice_number' => $request->invoice_number,
                    'paid_amount' => $request->paid_amount,
                    'status' => $request->status,
                    'title' => $request->title,
                    'enquiry_source_id' => $request->enquiry_source_id,
                    'car_type_id' => $request->car_type_id,
                    'pick_up_date' => $request->pick_up_date,
                    'drop_off_date' => $request->drop_off_date,
                    'pick_up_location' => $request->pick_up_location,
                    'drop_off_location' => $request->drop_off_location,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'special_requests' => $request->special_requests,
                ]
            );
        }
        // $response = $this->carEnquireRepo->update($request->id, $data);
        //EmployeeCarEnquiryEvent::dispatch($response);

        if ($carEnquiry->id) {
            return $this->success(new CarEnquireResource($carEnquiry), 'Car Enquiry Updated Successfully');
        } else {
            return $this->success(new CarEnquireResource($carEnquiry), 'Car Enquiry Created Successfully');
        }
    }

    public function delete($id)
    {
        // Find the car enquiry by ID
        $carEnquiry = CarEnquiry::find($id);

        if (!$carEnquiry) {
            return response()->json([
                'message' => 'Car Enquiry record not found',
            ], 500);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Check if the user has permission to delete (only admins or super admins can delete)
        if (!in_array($user->role_id, [1, 4])) { // Assuming role_id 1 is Admin and 4 is Super Admin
            return response()->json([
                'message' => 'You do not have permission to delete this car enquiry.',
            ], 403); // 403 Forbidden
        }

        // Proceed to delete the car enquiry
        $carEnquiry->delete();

        return $this->success([
            'flash_type' => 'success',
            'flash_message' => 'Car Enquiry deleted successfully',
            'flash_description' => $carEnquiry->title
        ]);
    }
}
