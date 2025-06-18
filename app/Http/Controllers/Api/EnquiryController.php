<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\EnquiriesRepository;
use App\Http\Resources\EnquiryResource;
use App\Repositories\PackageRepository;
use App\Http\Resources\PackageResource;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Enquiry;
use App\Models\MultiPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Events\EmployeeEvent;
use App\Events\EmployeeMultiCityEvent;
use App\Events\FollowUpNotificationEvent;
use App\Http\Requests\flightEnquiryMultiCityRequest;
use App\Http\Requests\flightEnquirySingleCityRequest;
use App\Jobs\SendFollowUpMessage;
use App\Jobs\SendFollowUpNotification;
use App\Models\PhoneNumber;
use App\Models\User;
use App\Notifications\CallReminderNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Pusher\Pusher;
use App\Events\FollowUpMessageEvent;


class EnquiryController extends BaseController
{
	protected $enquiriesRepo;
	protected $packageRepo;

	public function __construct(EnquiriesRepository $enquiriesRepo, PackageRepository $packageRepo)
	{
		$this->enquiriesRepo = $enquiriesRepo;
		$this->packageRepo = $packageRepo;
	}

	public function allEnquiries(Request $request)
	{
		$limit = $request->has('limit') ? $request->limit : 1000;
		$response = $this->enquiriesRepo->filter()->with([
			'packages' => function ($query) {
				$query->whereNull('parent_id')->with('children');
			},
			'children.packages.children' => function ($query) {
				$query->whereNull('parent_id')->with('children', 'children.parent');
			},
			'invoices',
			'invoices.supplier',
			'invoices.agentUser',
			'invoices.transactionType',
			'invoices.airLine',
			'supplier',
			'children.supplier',
			'transactionType',
			'assignedToUser',
			'packages.fromAirport',
			'packages.toAirport',
			'packages.children.fromAirport',
			'packages.children.toAirport',
			'airLine',
			'enquiryStatus',
			'enquirySource',
			// 'enquiryPaymentStatus',
			'createdByUser',
			'updatedByUser',
			'parent',
			'children',
			'children.parent',
			'children.packages.fromAirport',
			'children.packages.toAirport',
			'children.assignedToUser',
			'children.transactionType',
			'children.packages',
			'children.airLine',
			'children.fromAirport',
			'children.toAirport',
			'children.enquiryStatus',
			'children.enquirySource',
			'children.updatedByUser',
			'children.createdByUser',
			'fromAirport',
			'toAirport'
		])->whereNull('parent_id')->latest()->paginate($limit);

		$enquiriesWithPending = $response->map(function ($enquiry) {
			$enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
			$enquiry->overPaidAmount = max(0, $enquiry->paid_amount - $enquiry->budget);
			return $enquiry;
		});
		return $this->successWithPaginateData(EnquiryResource::collection($enquiriesWithPending), $response);
	}

	public function list(Request $request)
	{
		$user = auth()->user();
		$limit = $request->has('limit') ? $request->limit : 1000;

		$query = $this->enquiriesRepo->filter()
			->with(
				[
					'packages' => function ($query) {
						$query->whereNull('parent_id')->with('children');
					},
					'children.packages.children' => function ($query) {
						$query->whereNull('parent_id')->with('children', 'children.parent');
					},
					'invoices',
					'invoices.supplier',
					'invoices.agentUser',
					'invoices.transactionType',
					'invoices.airLine',
					'supplier',
					'children.supplier',
					'transactionType',
					'assignedToUser',
					'packages',
					'airLine',
					'enquiryStatus',
					'enquirySource',
					// 'enquiryPaymentStatus',
					'createdByUser',
					'updatedByUser',
					'parent',
					'children',
					'children.parent',
					'children.assignedToUser',
					'children.transactionType',
					'children.packages',
					'children.airLine',
					'children.enquiryStatus',
					'children.enquirySource',
					'children.updatedByUser',
					'children.createdByUser',
				]
			)->whereNull('parent_id')->where('created_by_user_id', $user->id);

		// if (!in_array($user->role_id, [1, 4])) {
		//     $query->where('assigned_to_user_id', $user->id);
		// }
		$response = $query->latest()->paginate($limit);
		$enquiriesWithPending = $response->map(function ($enquiry) {
			$enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
			$enquiry->overPaidAmount = max(0, $enquiry->paid_amount - $enquiry->budget);
			return $enquiry;
		});

		return $this->successWithPaginateData(EnquiryResource::collection($enquiriesWithPending), $response);
	}

	public function assignedEnquiryList(Request $request)
	{
		$user = auth()->user();
		$limit = $request->has('limit') ? $request->limit : 1000;

		$query = $this->enquiriesRepo->filter()
			->with(
				'invoices.supplier',
				'invoices.agentUser',
				'invoices.transactionType',
				'invoices.airLine',
				'invoices',
				'transactionType',
				'assignedToUser',
				'packages',
				'airLine',
				'supplier',
				'children.supplier',
				'enquiryStatus',
				'enquirySource',
				'createdByUser',
				'updatedByUser',
				'parent',
				'children',
				'children.parent',
				'children.assignedToUser',
				'children.transactionType',
				'children.packages',
				'children.airLine',
				'children.enquiryStatus',
				'children.enquirySource',
				'children.updatedByUser',
				'children.createdByUser',
			);

		// if (!in_array($user->role_id, [1, 4])) {
		//     $query->where('assigned_to_user_id', $user->id);
		// }
		$query->where('assigned_to_user_id', $user->id);

		$response = $query->latest()->paginate($limit);

		return $this->successWithPaginateData(EnquiryResource::collection($response), $response);
	}
	public function saveAndUpdateOne(flightEnquirySingleCityRequest $request)
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
			$parentEnquiry = Enquiry::find($request->id);
			$adminPaymentStatus = $parentEnquiry->admin_payment_status;
			if ($request->paid_amount > 0) {
				$adminPaymentStatus = 'pending';
			}
		} else {
			$adminPaymentStatus = 'no_action';
			if ($request->paid_amount > 0) {
				$adminPaymentStatus = 'pending';
			}
		}


		// Create or update the enquiry
		if ($request->id && $request->assigned_to_user_id) {
			$parentEnquiry = Enquiry::find($request->id);
			$parentEnquiry->supplier_id = $request->supplier_id;
			$parentEnquiry->transaction_type_id = $request->transaction_type_id;
			$parentEnquiry->assigned_to_user_id = $request->assigned_to_user_id;
			$parentEnquiry->updated_by_user_id = $user->id;
			$parentEnquiry->title = $request->title;
			$parentEnquiry->budget = $request->budget;
			$parentEnquiry->enquiry_source_id = $request->enquiry_source_id;
			// $parentEnquiry->enquiry_payment_status_id = $request->enquiry_payment_status_id;
			$parentEnquiry->enquiry_payment_status = $enquiryPaymentStatus;
			$parentEnquiry->enquiry_status_id = $request->enquiry_status_id;
			$parentEnquiry->customer_name = $request->customer_name;
			$parentEnquiry->phone_number = $request->phone_number;
			$parentEnquiry->email = $request->email;
			$parentEnquiry->booking_reference = $request->booking_reference;
			$parentEnquiry->invoice_number = $request->invoice_number;
			$parentEnquiry->remark = $request->remark;
			$parentEnquiry->type = $request->type;
			$parentEnquiry->air_line_id = $request->air_line_id;
			$parentEnquiry->package_type = $request->package_type;
			$parentEnquiry->from = $request->from;
			$parentEnquiry->to = $request->to;
			$parentEnquiry->departure_date = $request->departure_date;
			$parentEnquiry->return_date = $request->return_date;
			$parentEnquiry->adult = $request->adult;
			$parentEnquiry->child = $request->child;
			$parentEnquiry->infant = $request->infant;
			$parentEnquiry->status = $request->status ? $request->status : $parentEnquiry->status;
			$parentEnquiry->class_of_travel = $request->class_of_travel;
			// $parentEnquiry->created_at =  Carbon::now();
			$parentEnquiry->updated_at =  Carbon::now();
			$parentEnquiry->created_at = $parentEnquiry->created_at;
			$parentEnquiry->followed_up_at = $request->followed_up_at ? $request->followed_up_at : $parentEnquiry->followed_up_at;

			if ($request->paid_amount > $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} elseif ($request->paid_amount < $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} else {
				$parentEnquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			}
			$parentEnquiry->paid_amount = $request->paid_amount;
			$parentEnquiry->save();
		}

		if ($request->id) {
			$parentEnquiry = Enquiry::find($request->id);
			// dd($parentEnquiry->created_at);
			$enquiry = Enquiry::Create(
				[
					'updated_by_user_id' => $user->id,
					'parent_id' => $request->id,
					'supplier_id' => $request->supplier_id,
					'transaction_type_id' => $request->transaction_type_id,
					'created_by_user_id' => $user->id,
					'title' => $request->title,
					'budget' => $request->budget,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'type' => $request->type,
					'air_line_id' => $request->air_line_id,
					'package_type' => $request->package_type,
					'from' => $request->from,
					'to' => $request->to,
					'status' => $request->status ? $request->status : $parentEnquiry->status,
					'departure_date' => $request->departure_date,
					'return_date' => $request->return_date,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					'admin_payment_status' => $parentEnquiry->admin_payment_status,
					'class_of_travel' => $request->class_of_travel,
					'followed_up_at' => $request->followed_up_at,
					///'admin_payment_status' => $adminPaymentStatus,
					'created_at' => $parentEnquiry->created_at,
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $parentEnquiry->id), // Generate code

					'updated_at' => now(),
				]
			);
			// if ($request->paid_amount > $parentEnquiry->paid_amount) {
			//     $enquiry->admin_payment_status = 'pending';
			// } elseif ($request->paid_amount < $parentEnquiry->paid_amount) {
			//     $enquiry->admin_payment_status = 'no_action';
			// } else {
			//     $enquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			// }
			$enquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			$enquiry->created_at = $parentEnquiry->created_at;
			$enquiry->save();
		} else {
			$enquiry = Enquiry::Create(
				[
					'created_by_user_id' => $user->id,
					'transaction_type_id' => $request->transaction_type_id,
					'supplier_id' => $request->supplier_id,
					'title' => $request->title,
					'budget' => $request->budget,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'type' => $request->type,
					'air_line_id' => $request->air_line_id,
					'package_type' => $request->package_type,
					'from' => $request->from,
					'to' => $request->to,
					'status' => $request->status,
					'departure_date' => $request->departure_date,
					'return_date' => $request->return_date,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					'class_of_travel' => $request->class_of_travel,
					// 'followed_up_at' => $request->followed_up_at ?: 0,
					'followed_up_at' => $request->followed_up_at,
					'admin_payment_status' => $adminPaymentStatus,
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(),
					'updated_at' => null,
					'created_at' => now(),
				]
			);
			// create the parent enquiry as a history
			$childEnquiryHistory = Enquiry::Create(
				[
					'transaction_type_id' => $request->transaction_type_id,
					'updated_by_user_id' => $user->id,
					'supplier_id' => $request->supplier_id,
					'parent_id' => $enquiry->id,
					'created_by_user_id' => $user->id,
					'title' => $request->title,
					'budget' => $request->budget,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'type' => $request->type,
					'air_line_id' => $request->air_line_id,
					'package_type' => $request->package_type,
					'from' => $request->from,
					'to' => $request->to,
					'status' => $request->status ? $request->status : $parentEnquiry->status,
					'departure_date' => $request->departure_date,
					'return_date' => $request->return_date,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					'class_of_travel' => $request->class_of_travel,
					'followed_up_at' => $request->followed_up_at,
					'admin_payment_status' => $adminPaymentStatus,
					// 'enquiry_code' => Enquiry::generateEnquiryCode(true, $enquiry->id),
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id),
					'created_at' =>  $enquiry->created_at,
					'updated_at' => now(),
				]
			);

			$phone_number = PhoneNumber::where('phone_number', $request->phone_number)->first();
			if (!$phone_number) {
				$phone_number = PhoneNumber::create([
					'phone_number' => $request->phone_number,
					'enquiry_id' => $enquiry->id

				]);
			}


			// if ($request->paid_amount) {
			//     $childEnquiryHistory->admin_payment_status = $adminPaymentStatus;
			// } else {
			//     $childEnquiryHistory->admin_payment_status = 'no_action';
			// }
			// $childEnquiryHistory->save();
			if ($request->assigned_to_user_id == $user->id) {
				$enquiry->update(['assigned_to_user_id' => $user->id, 'status' => 'accept']);
				$enquiry->save();
			}
		}


		// Send notification
		// $user->notify(new CallReminderNotification($enquiry));

		///EmployeeEvent::dispatch($enquiry);


		// Return success message based on whether it is a create or update
		if ($request->id) {
			return $this->success(new EnquiryResource($enquiry), 'Enquiry updated successfully');
		} else {
			return $this->success(new EnquiryResource($enquiry), 'Enquiry created successfully');
		}
	}

	public function saveAndUpdateMultiple(flightEnquiryMultiCityRequest $request)
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
			$parentEnquiry = Enquiry::find($request->id);
			$adminPaymentStatus = $parentEnquiry->admin_payment_status;
			if ($request->paid_amount > 0) {
				$adminPaymentStatus = 'pending';
			}
		} else {
			$adminPaymentStatus = 'no_action';
			if ($request->paid_amount > 0) {
				$adminPaymentStatus = 'pending';
			}
		}


		if ($request->id && $request->assigned_to_user_id) {
			$parentEnquiry = Enquiry::find($request->id);
			$parentEnquiry->supplier_id = $request->supplier_id;
			$parentEnquiry->transaction_type_id = $request->transaction_type_id;
			$parentEnquiry->assigned_to_user_id = $request->assigned_to_user_id;
			$parentEnquiry->updated_by_user_id = $user->id;
			$parentEnquiry->title = $request->title;
			$parentEnquiry->budget = $request->budget;
			$parentEnquiry->enquiry_source_id = $request->enquiry_source_id;
			$parentEnquiry->enquiry_payment_status = $enquiryPaymentStatus;
			$parentEnquiry->enquiry_status_id = $request->enquiry_status_id;
			$parentEnquiry->customer_name = $request->customer_name;
			$parentEnquiry->phone_number = $request->phone_number;
			$parentEnquiry->email = $request->email;
			$parentEnquiry->booking_reference = $request->booking_reference;
			$parentEnquiry->invoice_number = $request->invoice_number;
			$parentEnquiry->remark = $request->remark;
			$parentEnquiry->type = $request->type;
			$parentEnquiry->air_line_id = $request->air_line_id;
			$parentEnquiry->from = $request->from;
			$parentEnquiry->to = $request->to;
			$parentEnquiry->status = $request->status ? $request->status : $parentEnquiry->status;
			$parentEnquiry->departure_date = $request->departure_date;
			$parentEnquiry->return_date = $request->return_date;
			$parentEnquiry->adult = $request->adult;
			$parentEnquiry->child = $request->child;
			$parentEnquiry->infant = $request->infant;
			$parentEnquiry->class_of_travel = $request->class_of_travel;
			// $parentEnquiry->created_at =  Carbon::now();
			$parentEnquiry->updated_at =  Carbon::now();
			$parentEnquiry->followed_up_at = $request->followed_up_at;
			$parentEnquiry->updated_at = \now();

			if ($request->paid_amount > $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} elseif ($request->paid_amount < $parentEnquiry->paid_amount) {
				$parentEnquiry->admin_payment_status = 'pending';
			} else {
				$parentEnquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			}
			$parentEnquiry->paid_amount = $request->paid_amount;

			$parentEnquiry->save();

			// Process packages
			// $packageIds = [];
			// if ($request->packages) {
			//     foreach ($request->packages as $packageData) {
			//         if (isset($packageData['id'])) {
			//             $packageId = $packageData['id'];

			//             $package = Package::findOrFail($packageId);
			//             // dd($parentEnquiry->id);
			//             if ($package->enquiry_id == $parentEnquiry->id) {
			//                 // Update existing package
			//                 //$package = Package::findOrFail($packageData['id']);
			//                 if ($package->enquiry_id == $parentEnquiry->id) {
			//                     $package->update([
			//                         'departure_date' => $packageData['departure_date'],
			//                         'from' => $packageData['from'],
			//                         'to' => $packageData['to'],
			//                     ]);
			//                     $packageIds[] = $package->id;
			//                 }
			//             }
			//         } else {
			//             // Create new package
			//             $newPackage = Package::create([
			//                 'enquiry_id' => $parentEnquiry->id,
			//                 'package_type' => 'multi_city',
			//                 'departure_date' => $packageData['departure_date'],
			//                 'from' => $packageData['from'],
			//                 'to' => $packageData['to'],
			//             ]);
			//             $packageIds[] = $newPackage->id;
			//         }
			//     }
			// }

			// Delete packages not included in the request
			// Package::where('enquiry_id', $parentEnquiry->id)
			//     ->whereNotIn('id', $packageIds)
			//     ->delete();
		}

		if ($request->id) {
			$parentEnquiry = Enquiry::find($request->id);
			$enquiry = Enquiry::Create(
				[
					'supplier_id' => $request->supplier_id,
					'transaction_type_id' => $request->transaction_type_id,
					'updated_by_user_id' => $user->id,
					'parent_id' => $request->id,
					'package_type' => "multi_city",
					'budget' => $request->budget,
					'title' => $request->title,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'air_line_id' => $request->air_line_id,

					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'status' => $request->status ? $request->status : $parentEnquiry->status,
					'followed_up_at' => $request->followed_up_at,
					'type' => $request->type,
					'class_of_travel' => $request->class_of_travel,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					// 'admin_payment_status' => $parentEnquiry->admin_payment_status,
					'updated_at' => now(),
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $parentEnquiry->id),
					// 'created_at' => $parentEnquiry->created_at,
					'created_at' => now(),

				]
			);
			$enquiry->admin_payment_status = $parentEnquiry->admin_payment_status;
			$enquiry->created_at = now();
			$enquiry->save();

			$packageIds = [];
			if ($request->packages) {
				foreach ($request->packages as $packageData) {
					$newPackage = Package::create([
						'enquiry_id' => $enquiry->id,
						'package_type' => 'multi_city',
						'departure_date' => $packageData['departure_date'],
						'from' => $packageData['from'],
						'to' => $packageData['to'],
					]);
				}
			}

			// Delete packages not included in the request
			// Package::where('enquiry_id', $enquiry->id)
			//     ->whereNotIn('id', $packageIds)
			//     ->delete();
		} else {
			$enquiry = Enquiry::Create(
				[
					'supplier_id' => $request->supplier_id,
					'transaction_type_id' => $request->transaction_type_id,
					'created_by_user_id' => $user->id,
					'package_type' => "multi_city",
					'budget' => $request->budget,
					'title' => $request->title,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'air_line_id' => $request->air_line_id,

					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'status' => $request->status,

					'type' => $request->type,
					'class_of_travel' => $request->class_of_travel,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					'followed_up_at' => $request->followed_up_at,
					'admin_payment_status' => $adminPaymentStatus,
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(),
					'created_at' => \now(),
					'updated_at' => \null,

				]
			);
			$childEnquiryHistory = Enquiry::Create(
				[
					'supplier_id' => $request->supplier_id,
					'transaction_type_id' => $request->transaction_type_id,
					'updated_by_user_id' => $user->id,
					'parent_id' => $enquiry->id,
					'package_type' => "multi_city",
					'budget' => $request->budget,
					'title' => $request->title,
					'enquiry_source_id' => $request->enquiry_source_id,
					'enquiry_payment_status' => $enquiryPaymentStatus,
					'assigned_to_user_id' => $request->assigned_to_user_id,
					'air_line_id' => $request->air_line_id,
					'enquiry_status_id' => $request->enquiry_status_id,
					'customer_name' => $request->customer_name,
					'phone_number' => $request->phone_number,
					'email' => $request->email,
					'booking_reference' => $request->booking_reference,
					'invoice_number' => $request->invoice_number,
					'remark' => $request->remark,
					'paid_amount' => $request->paid_amount,
					'status' => $request->status ? $request->status : $parentEnquiry->status,
					'type' => $request->type,
					'class_of_travel' => $request->class_of_travel,
					'adult' => $request->adult,
					'child' => $request->child,
					'infant' => $request->infant,
					'followed_up_at' => $request->followed_up_at,
					'admin_payment_status' => $enquiry->admin_payment_status,
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id),
					//'created_at' => $enquiry->created_at,
					'created_at' => now(),
					'updated_at' => \now(),
				]
			);

			if ($request->assigned_to_user_id == $user->id) {
				$enquiry->update(['assigned_to_user_id' => $user->id, 'status' => 'accept']);
				$enquiry->save();
			}
			if ($request->packages) {
				foreach ($request->packages as $packageData) {
					$newPackage = Package::create([
						'enquiry_id' => $enquiry->id,
						'package_type' => 'multi_city',
						'departure_date' => $packageData['departure_date'],
						'from' => $packageData['from'],
						'to' => $packageData['to'],
					]);
				}
			}
			if ($request->packages) {
				foreach ($request->packages as $packageData) {
					$newPackage = Package::create([
						'enquiry_id' => $childEnquiryHistory->id,
						'package_type' => 'multi_city',
						'departure_date' => $packageData['departure_date'],
						'from' => $packageData['from'],
						'to' => $packageData['to'],
					]);
				}
			}
			$phone_number = PhoneNumber::where('phone_number', $request->phone_number)->first();
			if (!$phone_number) {
				$phone_number = PhoneNumber::create([
					'phone_number' => $request->phone_number,
					'enquiry_id' => $enquiry->id

				]);
			}
		}

		// Reload enquiry with packages after creating/updating
		$enquiry->load('packages');

		// Dispatch event after loading packages
		//EmployeeMultiCityEvent::dispatch($enquiry);
		// Return the enquiry with packages in the response
		if ($request->id) {
			return $this->success(new EnquiryResource($enquiry), 'Enquiry updated Successfully');
		} else {
			return $this->success(new EnquiryResource($enquiry), 'Enquiry Created Successfully');
		}
	}

	public function delete($id)
	{
		// Find the enquiry by ID
		$enquiry = Enquiry::find($id);

		if (!$enquiry) {
			return response()->json([
				'message' => 'Enquiry record not found',
			], 500);
		}

		// Get the authenticated user
		$user = auth()->user();

		if (!in_array($user->role_id, [1, 4])) { // Assuming role_id 1 is Admin and 4 is Super Admin
			return response()->json([
				'message' => 'You do not have permission to delete this enquiry.',
			], 403);
		}

		$enquiry->delete();
		return $this->success([
			'flash_type' => 'success',
			'flash_message' => 'Enquiry deleted successfully',
			'flash_description' => $enquiry->customer_name
		]);
	}

	public function acceptOrReject(Request $request, $id)
	{

		$userId = auth()->user();
		$request->validate([
			'status' => 'required|string|in:reject,accept,pending',
		]);


		try {
			$enquiry = Enquiry::findOrFail($id);
		} catch (ModelNotFoundException $e) {
			return response()->json([
				'status' => false,
				'message' => "Hotel Enquiry with ID {$id} not found",
			], 404);
		}
		if ($enquiry->package_type == 'multi_city') {
			if ($enquiry && $request->status == 'reject' && $enquiry->assigned_to_user_id == $userId->id) {
				$enquiry->assigned_to_user_id = null;
				$enquiry->status = $request->status;
				$enquiry->save();

				$enquiry = Enquiry::with(['children' => function ($query) {
					$query->latest('created_at');
				}])->where('package_type', 'multi_city')->findOrFail($id);

				$latestChildEnquiry = $enquiry->children->first();

				$childEnquiry = Enquiry::Create(
					[
						'supplier_id' => $enquiry->supplier_id,
						'updated_by_user_id' => $userId->id,
						'created_by_user_id' => $userId->id,
						'transaction_type_id' => $enquiry->transaction_type_id,
						'parent_id' => $enquiry->id,
						'title' => $enquiry->title,
						'budget' => $enquiry->budget,
						'enquiry_source_id' => $enquiry->enquiry_source_id,
						// 'enquiry_payment_status' => $enquiry->enquiry_payment_status,
						'assigned_to_user_id' => $enquiry->assigned_to_user_id,
						'enquiry_status_id' => $enquiry->enquiry_status_id,
						'customer_name' => $enquiry->customer_name,
						'phone_number' => $enquiry->phone_number,
						'email' => $enquiry->email,
						'booking_reference' => $enquiry->booking_reference,
						'invoice_number' => $enquiry->invoice_number,
						'remark' => $enquiry->remark,
						'paid_amount' => $enquiry->paid_amount,
						'type' => $enquiry->type,
						'air_line_id' => $enquiry->air_line_id,
						'package_type' => $enquiry->package_type,
						'from' => $enquiry->from,
						'to' => $enquiry->to,
						'status' => $request->status,
						'departure_date' => $enquiry->departure_date,
						'return_date' => $enquiry->return_date,
						'adult' => $enquiry->adult,
						'child' => $enquiry->child,
						'infant' => $enquiry->infant,
						'class_of_travel' => $enquiry->class_of_travel,
						'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id), // Generate code
					]
				);

				foreach ($latestChildEnquiry->packages as $package) {
					$childEnquiry->packages()->create([
						'parent_id' => $package->id,
						'enquiry_id' => $childEnquiry->id,
						'package_type' => $package->package_type,
						'departure_date' => $package->departure_date,
						'from' => $package->from,
						'to' => $package->to,
						'created_at' => $package->created_at,
						'updated_at' => now(),
					]);
				}

				return $this->success([
					'data' => new EnquiryResource($childEnquiry),
					'flash_type' => 'success',
					'flash_message' => 'Enquiry status make reject successfully',
					'flash_description' => $enquiry->customer_name
				]);
			} else {
				$enquiry->status = $request->status;
				$enquiry->save();

				$enquiry = Enquiry::with(['children' => function ($query) {
					$query->latest('created_at');
				}])->where('package_type', 'multi_city')->findOrFail($id);

				$latestChildEnquiry = $enquiry->children->first();

				$childEnquiry = Enquiry::Create(
					[
						'supplier_id' => $enquiry->supplier_id,
						'transaction_type_id' => $enquiry->transaction_type_id,
						'updated_by_user_id' => $userId->id,
						'created_by_user_id' => $userId->id,
						'parent_id' => $enquiry->id,
						'title' => $enquiry->title,
						'budget' => $enquiry->budget,
						'enquiry_source_id' => $enquiry->enquiry_source_id,
						// 'enquiry_payment_status' => $enquiry->enquiry_payment_status,
						'assigned_to_user_id' => $enquiry->assigned_to_user_id,
						'enquiry_status_id' => $enquiry->enquiry_status_id,
						'customer_name' => $enquiry->customer_name,
						'phone_number' => $enquiry->phone_number,
						'email' => $enquiry->email,
						'booking_reference' => $enquiry->booking_reference,
						'invoice_number' => $enquiry->invoice_number,
						'remark' => $enquiry->remark,
						'paid_amount' => $enquiry->paid_amount,
						'type' => $enquiry->type,
						'air_line_id' => $enquiry->air_line_id,
						'package_type' => $enquiry->package_type,
						'from' => $enquiry->from,
						'to' => $enquiry->to,
						'status' => $request->status,
						'departure_date' => $enquiry->departure_date,
						'return_date' => $enquiry->return_date,
						'adult' => $enquiry->adult,
						'child' => $enquiry->child,
						'infant' => $enquiry->infant,
						'class_of_travel' => $enquiry->class_of_travel,
					]
				);
				foreach ($latestChildEnquiry->packages as $package) {
					$childEnquiry->packages()->create([
						'parent_id' => $package->id,
						'enquiry_id' => $childEnquiry->id,
						'package_type' => $package->package_type,
						'departure_date' => $package->departure_date,
						'from' => $package->from,
						'to' => $package->to,
						'created_at' => $package->created_at,
						'updated_at' => now(),
					]);
				}

				return $this->success([
					'data' => new EnquiryResource($childEnquiry),
					'flash_type' => 'success',
					'flash_message' => 'Enquiry status updated successfully',
					'flash_description' => $enquiry->customer_name
				]);
			}
		} else {
			if ($enquiry && $request->status == 'reject' && $enquiry->assigned_to_user_id == $userId->id) {
				$enquiry->assigned_to_user_id = null;
				$enquiry->status = $request->status;
				$enquiry->save();

				$childEnquiry = Enquiry::Create(
					[
						'supplier_id' => $enquiry->supplier_id,
						'updated_by_user_id' => $userId->id,
						'created_by_user_id' => $userId->id,
						'transaction_type_id' => $enquiry->transaction_type_id,
						'parent_id' => $enquiry->id,
						'title' => $enquiry->title,
						'budget' => $enquiry->budget,
						'enquiry_source_id' => $enquiry->enquiry_source_id,
						// 'enquiry_payment_status' => $enquiry->enquiry_payment_status,
						'assigned_to_user_id' => $enquiry->assigned_to_user_id,
						'enquiry_status_id' => $enquiry->enquiry_status_id,
						'customer_name' => $enquiry->customer_name,
						'phone_number' => $enquiry->phone_number,
						'email' => $enquiry->email,
						'booking_reference' => $enquiry->booking_reference,
						'invoice_number' => $enquiry->invoice_number,
						'remark' => $enquiry->remark,
						'paid_amount' => $enquiry->paid_amount,
						'type' => $enquiry->type,
						'air_line_id' => $enquiry->air_line_id,
						'package_type' => $enquiry->package_type,
						'from' => $enquiry->from,
						'to' => $enquiry->to,
						'status' => $request->status,
						'departure_date' => $enquiry->departure_date,
						'return_date' => $enquiry->return_date,
						'adult' => $enquiry->adult,
						'child' => $enquiry->child,
						'infant' => $enquiry->infant,
						'class_of_travel' => $enquiry->class_of_travel,
						'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id), // Generate code

					]
				);
				return $this->success([
					'data' => new EnquiryResource($childEnquiry),
					'flash_type' => 'success',
					'flash_message' => 'Enquiry status make reject successfully',
					'flash_description' => $enquiry->customer_name
				]);
			} else {
				$enquiry->status = $request->status;
				$enquiry->save();
				$childEnquiry = Enquiry::Create(
					[
						'supplier_id' => $enquiry->supplier_id,
						'transaction_type_id' => $enquiry->transaction_type_id,
						'updated_by_user_id' => $userId->id,
						'created_by_user_id' => $userId->id,
						'parent_id' => $enquiry->id,
						'title' => $enquiry->title,
						'budget' => $enquiry->budget,
						'enquiry_source_id' => $enquiry->enquiry_source_id,
						// 'enquiry_payment_status' => $enquiry->enquiry_payment_status,
						'assigned_to_user_id' => $enquiry->assigned_to_user_id,
						'enquiry_status_id' => $enquiry->enquiry_status_id,
						'customer_name' => $enquiry->customer_name,
						'phone_number' => $enquiry->phone_number,
						'email' => $enquiry->email,
						'booking_reference' => $enquiry->booking_reference,
						'invoice_number' => $enquiry->invoice_number,
						'remark' => $enquiry->remark,
						'paid_amount' => $enquiry->paid_amount,
						'type' => $enquiry->type,
						'air_line_id' => $enquiry->air_line_id,
						'package_type' => $enquiry->package_type,
						'from' => $enquiry->from,
						'to' => $enquiry->to,
						'status' => $request->status,
						'departure_date' => $enquiry->departure_date,
						'return_date' => $enquiry->return_date,
						'adult' => $enquiry->adult,
						'child' => $enquiry->child,
						'infant' => $enquiry->infant,
						'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id),
						'class_of_travel' => $enquiry->class_of_travel,
					]
				);
				return $this->success([
					'data' => new EnquiryResource($childEnquiry),
					'flash_type' => 'success',
					'flash_message' => 'Enquiry status updated successfully',
					'flash_description' => $enquiry->customer_name
				]);
			}
		}
	}


	public function getFlightEnquiries(Request $request)
	{
		$request->validate([
			'email' => 'nullable|email',
			'booking_reference' => 'nullable|string',
			'phone_number' => 'nullable|string',
			'title' => 'nullable|string',
		]);
		if (!$request->hasAny(['email', 'booking_reference', 'phone_number', 'title'])) {
			$emptyPagination = new LengthAwarePaginator([], 0, 10, 1, [
				'path' => $request->url(),
				'query' => $request->query(),
			]);
			return $this->successWithPaginateData([], $emptyPagination, "No search criteria provided. Returning empty list.");
		}

		$query = Enquiry::query();

		if ($request->has('email')) {
			$query->where('email', 'LIKE', '%' . $request->email . '%');
		}
		if ($request->has('booking_reference')) {
			$query->where('booking_reference', 'LIKE', '%' . $request->booking_reference . '%');
		}
		if ($request->has('phone_number')) {
			$query->where('phone_number', 'LIKE', '%' . $request->phone_number . '%');
		}
		if ($request->has('title')) {
			$query->where('title', 'LIKE', '%' . $request->title . '%');
		}

		$limit = $request->has('limit') ? $request->limit : 1000;

		$response = $query->with(
			'assignedToUser',
			'packages',
			'airLine',
			'transactionType',
			'enquiryStatus',
			'enquirySource',
			'createdByUser',
			'updatedByUser',
			'parent',
			'children',
			'children.assignedToUser',
			'children.transactionType',
			'children.packages',
			'children.airLine',
			'children.enquiryStatus',
			'children.enquirySource',
			'children.updatedByUser',
			'children.createdByUser',
		)->whereNull('parent_id')->latest()->paginate($limit);

		if ($response->isEmpty()) {
			return $this->successWithPaginateData([], $response, "No enquiries found matching the criteria.");
		}

		return $this->successWithPaginateData(EnquiryResource::collection($response), $response);
	}

	public function acceptOrRejectByAdmin(Request $request, $id)
	{
		$user = auth()->user();
		$request->validate([
			'admin_payment_status' => 'required|string|in:approved,reject,no_action',
			'note' => 'nullable|string',
		]);


		$enquiry = Enquiry::findOrFail($id);
		if ($enquiry->package_type == 'multi_city') {
			$enquiry = Enquiry::with(['children' => function ($query) {
				$query->latest('created_at');
			}])->where('package_type', 'multi_city')->findOrFail($id);

			$latestChildEnquiry = $enquiry->children->first();
			// dd($latestChildEnquiry->packages);

			if ($enquiry && in_array($user->role_id, [1, 4])) {
				$childEnquiry = Enquiry::create([
					'supplier_id' => $enquiry->supplier_id,
					'updated_by_user_id' => $user->id,
					'created_by_user_id' => $user->id,
					'transaction_type_id' => $enquiry->transaction_type_id,
					'parent_id' => $enquiry->id,
					'title' => $enquiry->title,
					'budget' => $enquiry->budget,
					'enquiry_source_id' => $enquiry->enquiry_source_id,
					'assigned_to_user_id' => $enquiry->assigned_to_user_id,
					'enquiry_status_id' => $enquiry->enquiry_status_id,
					'customer_name' => $enquiry->customer_name,
					'phone_number' => $enquiry->phone_number,
					'email' => $enquiry->email,
					'booking_reference' => $enquiry->booking_reference,
					'invoice_number' => $enquiry->invoice_number,
					'remark' => $enquiry->remark,
					'paid_amount' => $enquiry->paid_amount,
					'type' => $enquiry->type,
					'air_line_id' => $enquiry->air_line_id,
					'package_type' => $enquiry->package_type,
					'from' => $enquiry->from,
					'to' => $enquiry->to,
					'status' => $enquiry->status,
					'departure_date' => $enquiry->departure_date,
					'return_date' => $enquiry->return_date,
					'adult' => $enquiry->adult,
					'child' => $enquiry->child,
					'infant' => $enquiry->infant,
					'admin_payment_status' => $request->admin_payment_status,
					'note' => $request->note,
					'class_of_travel' => $enquiry->class_of_travel,
					'created_at' => $enquiry->created_at, // Preserving parent created_at
					'updated_at' => now(), // New updated_at
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id),
				]);

				$enquiry->admin_payment_status = $request->admin_payment_status;
				$enquiry->note = $request->note;
				$enquiry->save();


				// Clone associated packages
				foreach ($latestChildEnquiry->packages as $package) {
					$childEnquiry->packages()->create([
						'parent_id' => $package->id,
						'enquiry_id' => $childEnquiry->id,
						'package_type' => $package->package_type,
						'departure_date' => $package->departure_date,
						'from' => $package->from,
						'to' => $package->to,
						'created_at' => $package->created_at,
						'updated_at' => now(),
					]);
				}

				return response()->json([
					'status' => true,
					'message' => 'Enquiry status updated successfully.',
					'data' => new EnquiryResource($childEnquiry),
				]);
			}
		} else {
			if ($enquiry && in_array($user->role_id, [1, 4])) {
				$childEnquiry = Enquiry::create([
					'supplier_id' => $enquiry->supplier_id,
					'updated_by_user_id' => $user->id,
					'created_by_user_id' => $user->id,
					'transaction_type_id' => $enquiry->transaction_type_id,
					'parent_id' => $enquiry->id,
					'title' => $enquiry->title,
					'budget' => $enquiry->budget,
					'enquiry_source_id' => $enquiry->enquiry_source_id,
					'assigned_to_user_id' => $enquiry->assigned_to_user_id,
					'enquiry_status_id' => $enquiry->enquiry_status_id,
					'customer_name' => $enquiry->customer_name,
					'phone_number' => $enquiry->phone_number,
					'email' => $enquiry->email,
					'booking_reference' => $enquiry->booking_reference,
					'invoice_number' => $enquiry->invoice_number,
					'remark' => $enquiry->remark,
					'paid_amount' => $enquiry->paid_amount,
					'type' => $enquiry->type,
					'air_line_id' => $enquiry->air_line_id,
					'package_type' => $enquiry->package_type,
					'from' => $enquiry->from,
					'to' => $enquiry->to,
					'status' => $enquiry->status,
					'departure_date' => $enquiry->departure_date,
					'return_date' => $enquiry->return_date,
					'adult' => $enquiry->adult,
					'child' => $enquiry->child,
					'infant' => $enquiry->infant,
					'admin_payment_status' => $request->admin_payment_status,
					'note' => $request->note,
					'class_of_travel' => $enquiry->class_of_travel,
					'created_at' => $enquiry->created_at,
					'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $enquiry->id),
					'updated_at' => now(),
				]);

				$enquiry->admin_payment_status = $request->admin_payment_status;
				$enquiry->note = $request->note;
				$enquiry->save();

				return response()->json([
					'status' => true,
					'message' => 'Enquiry status updated successfully.',
					'data' => new EnquiryResource($childEnquiry),
				]);
			}
		}
	}

	// public function followUp(Request $request , $id)
	// {
	//     $request->validate([
	//         'followed_up_at' => 'required|date|after_or_equal:now',
	//         'follow_up_message' => 'required|string|max:1000',
	//     ]);

	//     $enquiry = Enquiry::find($id);

	//     if (!$enquiry) {
	//         return response()->json(['message' => 'Enquiry not found'], 404);
	//     }

	//     $enquiry->update([
	//         'followed_up_at' => $request->followed_up_at,
	//         'follow_up_message' => $request->follow_up_message,
	//     ]);

	//     // Pusher setup
	//     $options = array(
	//         'cluster' => 'ap2',
	//         'useTLS' => true
	//     );

	//     $pusher = new Pusher(
	//         '91c8a0fa751bddeef247',
	//         '371e7ab4c09707bb4d9c',
	//         '1919831',
	//         $options
	//     );

	//     // $pusher = new Pusher(
	//     //     env('PUSHER_APP_KEY'),
	//     //     env('PUSHER_APP_SECRET'),
	//     //     env('PUSHER_APP_ID'),
	//     //     $options
	//     // );

	//     // Prepare the data to send with Pusher
	//     $data['message'] = $request->follow_up_message;
	//     $data['sender_id'] = auth()->id();
	//     $data['receiver_id'] = (int) $enquiry->assigned_to_user_id;
	//     $data['enquiry_id'] = $id;
	//     $data['enquiry_title'] = $enquiry->title;
	//     $data['remark'] =$enquiry->remark;


	//     // Trigger the Pusher event
	//     $pusher->trigger('my-channel', 'my-event', $data);

	//     // Broadcast the chat message event (if you want to broadcast through Laravel broadcasting as well)
	//     broadcast(new FollowUpMessageEvent($enquiry))->toOthers();

	//     // Return the chat message as a response
	//   //  return response()->json($enquiry, 201);

	//   return response()->json(['message' => 'Follow-up message scheduled successfully'], 200);
	// }

	public function followUp(Request $request, $id)
	{
		$request->validate([
			'followed_up_at' => 'required|date|after_or_equal:now',
			'follow_up_message' => 'required|string|max:1000',
		]);

		$enquiry = Enquiry::find($id);

		if (!$enquiry) {
			return response()->json(['message' => 'Enquiry not found'], 404);
		}

		$enquiry->update([
			'followed_up_at' => $request->followed_up_at,
			'follow_up_message' => $request->follow_up_message,
		]);


		// Schedule the follow-up notification
		$delay = now()->diffInSeconds($request->followed_up_at);
		SendFollowUpNotification::dispatch($enquiry)->delay($delay);

		// Pusher setup
		$options = array(
			'cluster' => 'ap2',
			'useTLS' => true
		);

		// $pusher = new Pusher(
		//     '91c8a0fa751bddeef247',
		//     '371e7ab4c09707bb4d9c',
		//     '1919831',
		//     $options
		// );

		$pusher = new Pusher(
			env('PUSHER_APP_KEY'),
			env('PUSHER_APP_SECRET'),
			env('PUSHER_APP_ID'),
			$options
		);

		// Prepare the data to send with Pusher
		$data['message'] = $request->follow_up_message;
		$data['sender_id'] = auth()->id();
		$data['receiver_id'] = (int) $enquiry->assigned_to_user_id;
		$data['enquiry_id'] = $id;
		$data['enquiry_title'] = $enquiry->title;
		$data['remark'] = $enquiry->remark;


		// Trigger the Pusher event
		$pusher->trigger('my-channel', 'my-event', $data);


		return response()->json(['message' => 'Follow-up message scheduled successfully'], 200);
	}
}
