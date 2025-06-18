<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\HotelEnquireRepository;
use App\Http\Resources\HotelEnquireResource;
use App\Models\HotelEnquire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\EmployeeHotelEnquiryEvent;
use App\Events\FollowupReminderSent;
use App\Http\Requests\HotelEnquiryRequest;
use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\FollowupMessage;

class HotelEnquireController extends BaseController
{
	protected $hotelEnquireRepo;

	public function __construct(HotelEnquireRepository $hotelEnquireRepo)
	{
		$this->hotelEnquireRepo = $hotelEnquireRepo;
	}

	public function list(Request $request)
	{
		$limit = $request->has('limit') ? $request->limit : 1000;
		$query = $this->hotelEnquireRepo->filter()->with(
			'transactionType',
			'createdByUser',
			'updatedByUser',
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
		$response = $query->latest()->paginate($limit);
		$enquiriesWithPending = $response->map(function ($enquiry) {
			$enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
			return $enquiry;
		});
		return $this->successWithPaginateData(HotelEnquireResource::collection($enquiriesWithPending), $response);
	}
	public function createdByUserList(Request $request)
	{
		$user = auth()->user();
		$limit = $request->has('limit') ? $request->limit : 1000;
		$query = $this->hotelEnquireRepo->filter()->with(
			'createdByUser',
			'transactionType',
			'updatedByUser',
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
		)->whereNull('parent_id')->where('created_by_user_id', $user->id);
		$response = $query->latest()->paginate($limit);
		$enquiriesWithPending = $response->map(function ($enquiry) {
			$enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
			return $enquiry;
		});
		return $this->successWithPaginateData(HotelEnquireResource::collection($enquiriesWithPending), $response);
	}
	public function saveAndUpdate(HotelEnquiryRequest $request)
	{
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

			$adminPaymentStatus = $parentEnquiry->admin_payment_status;
			if ($request->paid_amount > $parentEnquiry->paid_amount || $request->paid_amount < $parentEnquiry->paid_amount) {
				$adminPaymentStatus = 'pending';
			}
			// if ($request->paid_amount > $parentEnquiry->paid_amount) {
			//     $parentEnquiry->admin_payment_status = 'pending';
			//     $adminPaymentStatus = 'pending';
			// } elseif ($request->paid_amount < $parentEnquiry->paid_amount) {
			//     $parentEnquiry->admin_payment_status = 'pending';
			//     $adminPaymentStatus = 'pending';
			// } else {
			//     $parentEnquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			//     $adminPaymentStatus = $parentEnquiry->admin_payment_status;
			// }
		} else {
			$adminPaymentStatus = 'no_action';
			if ($request->paid_amount > 0) {
				$adminPaymentStatus = 'pending';
			}
		}

		// $data = $request->all();
		// $data['created_by_user_id'] = $user->id;
		if ($request->id && $request->assigned_to_user_id) {
			$parentEnquiry = HotelEnquire::find($request->id);
			$parentEnquiry->transaction_type_id = $request->transaction_type_id;
			$parentEnquiry->enquiry_status_id = $request->enquiry_status_id;
			$parentEnquiry->updated_by_user_id = $user->id;
			$parentEnquiry->assigned_to_user_id = $request->assigned_to_user_id;
			$parentEnquiry->enquiry_payment_status = $enquiryPaymentStatus;
			$parentEnquiry->budget = $request->budget;
			$parentEnquiry->invoice_number = $request->invoice_number;
			$parentEnquiry->paid_amount = $request->paid_amount;
			$parentEnquiry->status = $request->status ? $request->status : $parentEnquiry->status;
			$parentEnquiry->title = $request->title;
			$parentEnquiry->enquiry_source_id = $request->enquiry_source_id;
			$parentEnquiry->full_name = $request->full_name;
			$parentEnquiry->email = $request->email;
			$parentEnquiry->phone_number = $request->phone_number;
			$parentEnquiry->destination = $request->destination;
			$parentEnquiry->check_in_date = $request->check_in_date;
			$parentEnquiry->check_out_date = $request->check_out_date;
			$parentEnquiry->guest = $request->guest;
			$parentEnquiry->room = $request->room;
			$parentEnquiry->booking_reference = $request->booking_reference;
			$parentEnquiry->special_requests = $request->special_requests;

			if ($request->paid_amount > $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} elseif ($request->paid_amount < $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} else {
				$parentEnquiry->admin_payment_status = $adminPaymentStatus;
			}
			$parentEnquiry->save();
		}
		//dd($parentEnquiry);

		if ($request->id) {
			$hotelEnquiry = HotelEnquire::Create([
				'updated_by_user_id' => $user->id,
				'transaction_type_id' => $request->transaction_type_id,
				'enquiry_status_id' => $request->enquiry_status_id,
				'parent_id' => $request->id,
				'assigned_to_user_id' => $request->assigned_to_user_id,
				'enquiry_payment_status' => $enquiryPaymentStatus,
				'budget' => $request->budget,
				'invoice_number' => $request->invoice_number,
				'paid_amount' => $request->paid_amount,
				'status' => $request->status ? $request->status : $parentEnquiry->status,
				'title' => $request->title,
				'enquiry_source_id' => $request->enquiry_source_id,
				'full_name' => $request->full_name,
				'email' => $request->email,
				'phone_number' => $request->phone_number,
				'destination' => $request->destination,
				'check_in_date' => $request->check_in_date,
				'check_out_date' => $request->check_out_date,
				'guest' => $request->guest,
				'room' => $request->room,
				'booking_reference' => $request->booking_reference,
				'special_requests' => $request->special_requests,
				'admin_payment_status' => $parentEnquiry->admin_payment_status,

				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $request->id),

			]);
		} else {
			$hotelEnquiry = HotelEnquire::Create([
				'created_by_user_id' => $user->id,
				'transaction_type_id' => $request->transaction_type_id,
				'enquiry_status_id' => $request->enquiry_status_id,
				'assigned_to_user_id' => $request->assigned_to_user_id,
				'enquiry_payment_status' => $enquiryPaymentStatus,
				'budget' => $request->budget,
				'invoice_number' => $request->invoice_number,
				'paid_amount' => $request->paid_amount,
				'status' => $request->status,
				'title' => $request->title,
				'enquiry_source_id' => $request->enquiry_source_id,
				'full_name' => $request->full_name,
				'email' => $request->email,
				'phone_number' => $request->phone_number,
				'destination' => $request->destination,
				'check_in_date' => $request->check_in_date,
				'check_out_date' => $request->check_out_date,
				'guest' => $request->guest,
				'room' => $request->room,
				'booking_reference' => $request->booking_reference,
				'special_requests' => $request->special_requests,
				'admin_payment_status' => $adminPaymentStatus,
				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(),
			]);
			$childHotelEnquiry = HotelEnquire::Create([
				'transaction_type_id' => $request->transaction_type_id,
				'created_by_user_id' => $user->id,
				'updated_by_user_id' => $user->id,
				'parent_id' => $hotelEnquiry->id,
				'enquiry_status_id' => $request->enquiry_status_id,
				'assigned_to_user_id' => $request->assigned_to_user_id,
				'enquiry_payment_status' => $enquiryPaymentStatus,
				'budget' => $request->budget,
				'invoice_number' => $request->invoice_number,
				'paid_amount' => $request->paid_amount,
				'status' => $request->status,
				'title' => $request->title,
				'enquiry_source_id' => $request->enquiry_source_id,
				'full_name' => $request->full_name,
				'email' => $request->email,
				'phone_number' => $request->phone_number,
				'destination' => $request->destination,
				'check_in_date' => $request->check_in_date,
				'check_out_date' => $request->check_out_date,
				'guest' => $request->guest,
				'room' => $request->room,
				'booking_reference' => $request->booking_reference,
				'special_requests' => $request->special_requests,
				'admin_payment_status' => $adminPaymentStatus,
				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $hotelEnquiry->id),

			]);

			// $phoneNumber = PhoneNumber::Create([
			//     'phone_number' => $request->phone_number,
			//     'hotel_enquiry_id' => $hotelEnquiry->id,
			// ]);

			if ($request->assigned_to_user_id == $user->id) {
				$hotelEnquiry->update(['assigned_to_user_id' => $user->id, 'status' => 'accept']);
				$hotelEnquiry->save();
			}
		}
		// $response = $this->hotelEnquireRepo->update($request->id, $request);
		// //EmployeeHotelEnquiryEvent::dispatch($response);

		if ($hotelEnquiry->id) {
			return $this->success(new HotelEnquireResource($hotelEnquiry), 'Hotel Enquiry Updated Successfully');
		} else {
			return $this->success(new HotelEnquireResource($hotelEnquiry), 'Hotel Enquiry Created Successfully');
		}
	}

	public function acceptOrReject(Request $request, $id)
	{
		$userId = auth()->user();
		$request->validate([
			'status' => 'nullable|string|in:pending,accept,reject',
		]);
		try {
			$hotelEnquiry = HotelEnquire::findOrFail($id);
		} catch (ModelNotFoundException $e) {
			return response()->json([
				'status' => false,
				'message' => "Hotel Enquiry with ID {$id} not found",
			], 404);
		}

		if ($request->status == 'reject' && $hotelEnquiry->assigned_to_user_id == $userId->id) {
			$hotelEnquiry->assigned_to_user_id = null;
			$hotelEnquiry->status = $request->status;
			$hotelEnquiry->save();

			$childHotelEnquiry = HotelEnquire::Create([
				'created_by_user_id' => $userId->id,
				'updated_by_user_id' => $userId->id,
				'transaction_type_id' => $hotelEnquiry->transaction_type_id,
				'parent_id' => $hotelEnquiry->id,
				'enquiry_status_id' => $hotelEnquiry->enquiry_status_id,
				'assigned_to_user_id' => $hotelEnquiry->assigned_to_user_id,
				'enquiry_payment_status' => $hotelEnquiry->enquiry_payment_status,
				'budget' => $hotelEnquiry->budget,
				'invoice_number' => $hotelEnquiry->invoice_number,
				'paid_amount' => $hotelEnquiry->paid_amount,
				'status' => $request->status,
				'title' => $hotelEnquiry->title,
				'enquiry_source_id' => $hotelEnquiry->enquiry_source_id,
				'full_name' => $hotelEnquiry->full_name,
				'email' => $hotelEnquiry->email,
				'phone_number' => $hotelEnquiry->phone_number,
				'destination' => $hotelEnquiry->destination,
				'check_in_date' => $hotelEnquiry->check_in_date,
				'check_out_date' => $hotelEnquiry->check_out_date,
				'guest' => $hotelEnquiry->guest,
				'room' => $hotelEnquiry->room,
				'special_requests' => $hotelEnquiry->special_requests,
				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $hotelEnquiry->id),

			]);

			return $this->success([
				'flash_type' => 'success',
				'data' => new HotelEnquireResource($childHotelEnquiry),
				'flash_message' => 'Hotel Enquiry status set to reject successfully',
				'flash_description' => $hotelEnquiry->full_name
			]);
		} else {
			$hotelEnquiry->status = $request->status;
			$hotelEnquiry->save();
			$childHotelEnquiry = HotelEnquire::Create([
				'created_by_user_id' => $userId->id,
				'updated_by_user_id' => $userId->id,
				'parent_id' => $hotelEnquiry->id,
				'transaction_type_id' => $hotelEnquiry->transaction_type_id,
				'enquiry_status_id' => $hotelEnquiry->enquiry_status_id,
				'assigned_to_user_id' => $hotelEnquiry->assigned_to_user_id,
				'enquiry_payment_status' => $hotelEnquiry->enquiry_payment_status,
				'budget' => $hotelEnquiry->budget,
				'invoice_number' => $hotelEnquiry->invoice_number,
				'paid_amount' => $hotelEnquiry->paid_amount,
				'status' => $request->status,
				'title' => $hotelEnquiry->title,
				'enquiry_source_id' => $hotelEnquiry->enquiry_source_id,
				'full_name' => $hotelEnquiry->full_name,
				'email' => $hotelEnquiry->email,
				'phone_number' => $hotelEnquiry->phone_number,
				'destination' => $hotelEnquiry->destination,
				'check_in_date' => $hotelEnquiry->check_in_date,
				'check_out_date' => $hotelEnquiry->check_out_date,
				'guest' => $hotelEnquiry->guest,
				'room' => $hotelEnquiry->room,
				'special_requests' => $hotelEnquiry->special_requests,
				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $hotelEnquiry->id),

			]);

			return $this->success([
				'flash_type' => 'success',
				'data' => new HotelEnquireResource($childHotelEnquiry),
				'flash_message' => 'Hotel Enquiry status updated successfully',
				'flash_description' => $childHotelEnquiry->full_name
			]);
		}
	}


	public function delete($id)
	{
		// Find the hotel enquiry by ID
		$hotelEnquire = HotelEnquire::find($id);

		if (!$hotelEnquire) {
			return response()->json([
				'message' => 'Hotel Enquiry record not found',
			], 500);
		}

		// Get the authenticated user
		$user = auth()->user();

		// Check if the user has permission to delete (only admins or super admins can delete)
		if (!in_array($user->role_id, [1, 4])) { // Assuming role_id 1 is Admin and 4 is Super Admin
			return response()->json([
				'message' => 'You do not have permission to delete this hotel enquiry.',
			], 403); // 403 Forbidden
		}

		// Proceed to delete the hotel enquiry
		$hotelEnquire->delete();

		return $this->success([
			'flash_type' => 'success',
			'flash_message' => 'Hotel Enquiry deleted successfully',
			'flash_description' => $hotelEnquire->title
		]);
	}

	public function searchHotelEnquiries(Request $request)
	{

		$request->validate([
			'email' => 'nullable|email',
			'booking_reference' => 'nullable|string',
			'phone_number' => 'nullable|string',
			'title' => 'nullable|string',
		]);
		if (!$request->hasAny(['email', 'invoice_number', 'phone_number', 'title'])) {
			$emptyPagination = new LengthAwarePaginator([], 0, 10, 1, [
				'path' => $request->url(),
				'query' => $request->query(),
			]);
			return $this->successWithPaginateData([], $emptyPagination, "No search criteria provided. Returning empty list.");
		}

		$query = HotelEnquire::query();

		if ($request->has('email')) {
			$query->where('email', 'LIKE', '%' . $request->email . '%');
		}
		if ($request->has('invoice_number')) {
			$query->where('invoice_number', 'LIKE', '%' . $request->invoice_number . '%');
		}
		if ($request->has('phone_number')) {
			$query->where('phone_number', 'LIKE', '%' . $request->phone_number . '%');
		}
		if ($request->has('title')) {
			$query->where('title', 'LIKE', '%' . $request->title . '%');
		}

		$limit = $request->has('limit') ? $request->limit : 1000;

		$response = $query->with(
			'createdByUser',
			'updatedByUser',
			'parent',
			'transactionType',
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
		)->whereNull('parent_id')->latest()->paginate($limit);

		if ($response->isEmpty()) {
			return $this->successWithPaginateData([], $response, "No enquiries found matching the criteria.");
		}

		return $this->successWithPaginateData(HotelEnquireResource::collection($response), $response);
	}

	public function acceptOrRejectByAdmin(Request $request, $id)
	{
		$user = auth()->user();
		$request->validate([
			'admin_payment_status' => 'required|string|in:approved,pending,no_action',
			'note' => 'nullable|string',
		]);


		//$hotelEnquiry = HotelEnquire::findOrFail($id);
		$hotelEnquiry = $this->hotelEnquireRepo->update($id, $request);
		//dd($hotelEnquiry);
		if ($hotelEnquiry) {
			$request->merge([
				'parent_id' =>  $hotelEnquiry->id,
				'transaction_type_id' => $hotelEnquiry->transaction_type_id,
				'enquiry_status_id' => $hotelEnquiry->enquiry_status_id,
				'created_by_user_id' => $hotelEnquiry->created_by_user_id,
				'updated_by_user_id' => $user->id,
				'assigned_to_user_id' => $hotelEnquiry->assigned_to_user_id,
				'enquiry_source_id' => $hotelEnquiry->enquiry_source_id,
				'enquiry_payment_status_id' => $hotelEnquiry->enquiry_payment_status_id,
				'enquiry_type' => $hotelEnquiry->enquiry_type,
				'title' => $hotelEnquiry->title,
				'full_name' => $hotelEnquiry->full_name,
				'email' => $hotelEnquiry->email,
				'phone_number' => $hotelEnquiry->phone_number,
				'destination' => $hotelEnquiry->destination,
				'budget' => $hotelEnquiry->budget,
				'invoice_number' => $hotelEnquiry->invoice_number,
				'paid_amount' => $hotelEnquiry->paid_amount,
				'check_in_date' => $hotelEnquiry->check_in_date,
				'check_out_date' => $hotelEnquiry->check_out_date,
				'guest' => $hotelEnquiry->guest,
				'room' => $hotelEnquiry->room,
				'special_requests' => $hotelEnquiry->special_requests,
				'booking_reference' => $hotelEnquiry->booking_reference,
				'status' => $hotelEnquiry->status,
				//'enquiry_payment_status' => $request->admin_payment_status,
				'note' => $request->note,
				'enquiry_code' => $this->hotelEnquireRepo->generateEnquiryCode(true, $hotelEnquiry->id),

			]);
			$childHotelEnquires = $this->hotelEnquireRepo->store($request);
			return response()->json([
				'status' => true,
				'message' => 'Enquiry status updated successfully.',
				'hotelEnquiry' => new HotelEnquireResource($hotelEnquiry),
				'childHotelEnquires' => new HotelEnquireResource($childHotelEnquires),
			]);
		}
	}

	public function followUp(Request $request, $id)
	{

		$request->validate([
			'followed_up_at' => 'required|date|after_or_equal:now',
			'follow_up_message' => 'required|string|max:1000',
		]);

		$hotelEnquire = HotelEnquire::find($id);

		if (!$hotelEnquire) {
			return response()->json(['message' => 'Enquiry not found'], 404);
		}
		$this->hotelEnquireRepo->update($id, $request);
		$hotelEnquire->refresh();

		$followup = FollowupMessage::create([
			'enquiry_id' => $id,
			'followed_up_at' => $request->followed_up_at,
			'follow_up_message' => $request->follow_up_message,
			'receiver_id' => $request->user()->id,
		]);

		return response()->json(['message' => 'Follow-up message scheduled successfully'], 200);
	}
}
