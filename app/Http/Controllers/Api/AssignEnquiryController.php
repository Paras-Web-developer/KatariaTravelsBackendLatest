<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AssignedFlightOneEnquiryRequest;
use App\Http\Requests\AssignedFlightMultiEnquiryRequest;
use App\Repositories\EnquiriesRepository;
use App\Http\Resources\EnquiryResource;
use App\Repositories\PackageRepository;
use App\Http\Resources\PackageResource;
use Illuminate\Support\Carbon;
use App\Models\Enquiry;
use App\Models\MultiPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Events\EmployeeEvent;
use App\Events\EmployeeMultiCityEvent;

class AssignEnquiryController extends BaseController
{
    protected $enquiriesRepo;
    protected $packageRepo;

    public function __construct(EnquiriesRepository $enquiriesRepo, PackageRepository $packageRepo)
    {
        $this->enquiriesRepo = $enquiriesRepo;
        $this->packageRepo = $packageRepo;
    }



    public function assignedEnquiryList(Request $request)
    {
        $user = auth()->user();
        $limit = $request->has('limit') ? $request->limit : 10;

        $query = $this->enquiriesRepo->filter()
            ->with(
                'invoices.supplier',
                'invoices.agentUser',
                'invoices.transactionType',
                'invoices.airLine',
                'invoices',
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
            )->whereNull('parent_id');

        // if (!in_array($user->role_id, [1, 4])) {
        //     $query->where('assigned_to_user_id', $user->id);
        // }
        $query->where('assigned_to_user_id', $user->id);

        $response = $query->latest()->paginate($limit);
        $enquiriesWithPending = $response->map(function ($enquiry) {
            $enquiry->pendingAmount = max(0, $enquiry->budget - $enquiry->paid_amount);
            $enquiry->overPaidAmount = max(0, $enquiry->paid_amount - $enquiry->budget);
            return $enquiry;
        });

        return $this->successWithPaginateData(EnquiryResource::collection($enquiriesWithPending), $response);
    }
    public function saveAndUpdateOneAssignedEnquiry(AssignedFlightOneEnquiryRequest $request)
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
            $parentEnquiry = Enquiry::find($request->id);
            if (!$parentEnquiry) {
                return response()->json([
                    'status' => false,
                    'message' => "Enquiry with ID {$request->id} not found"
                ], 404);
            }
            $parentEnquiry->update([
                'supplier_id' => $request->supplier_id ? $request->supplier_id : $parentEnquiry->supplier_id,
                'updated_by_user_id' => $user->id,
                'transaction_type_id' => $request->transaction_type_id ? $request->transaction_type_id : $parentEnquiry->transaction_type_id,
                'title' => $request->title ? $request->title : $parentEnquiry->title,
                'budget' => $request->budget ? $request->budget : $parentEnquiry->budget,
                'enquiry_source_id' => $request->enquiry_source_id ? $request->enquiry_source_id : $parentEnquiry->enquiry_source_id,
                'enquiry_payment_status' => $enquiryPaymentStatus,
                'enquiry_status_id' => $request->enquiry_status_id ? $request->enquiry_status_id : $parentEnquiry->enquiry_status_id,
                'customer_name' => $request->customer_name ? $request->customer_name : $parentEnquiry->customer_name,
                'phone_number' => $request->phone_number ? $request->phone_number : $parentEnquiry->phone_number,
                'email' => $request->email ? $request->email : $parentEnquiry->email,
                'booking_reference' => $request->booking_reference ? $request->booking_reference : $parentEnquiry->booking_reference,
                'invoice_number' => $request->invoice_number ? $request->invoice_number : $parentEnquiry->invoice_number,
                'remark' => $request->remark ? $request->remark : $parentEnquiry->remark,
                'paid_amount' => $request->paid_amount ? $request->paid_amount : $parentEnquiry->paid_amount,
                'type' => $request->type ? $request->type : $parentEnquiry->type,
                'air_line_id' => $request->air_line_id ? $request->air_line_id : $parentEnquiry->air_line_id,
                'package_type' => $request->package_type ? $request->package_type : $parentEnquiry->package_type,
                'from' => $request->from ? $request->from : $parentEnquiry->from,
                'to' => $request->to ? $request->to : $parentEnquiry->to,
                'departure_date' => $request->departure_date ? $request->departure_date : $parentEnquiry->departure_date,
                'return_date' => $request->return_date ? $request->return_date : $parentEnquiry->return_date,
                'adult' => $request->adult ? $request->adult : $parentEnquiry->adult,
                'child' => $request->child ? $request->child : $parentEnquiry->child,
                'infant' => $request->infant ? $request->infant : $parentEnquiry->infant,
                'class_of_travel' => $request->class_of_travel ? $request->class_of_travel : $parentEnquiry->class_of_travel,
                'status' => $request->status ? $request->status : $parentEnquiry->status,
                // 'created_at' =>  Carbon::now(),
                'followed_up_at' => $request->followed_up_at ? $request->followed_up_at : $parentEnquiry->followed_up_at,
                'updated_at' =>  Carbon::now(),
            ]);
        }

        // Create or update the enquiry
        if ($request->id) {
            $enquiry = Enquiry::Create(
                [
                    'supplier_id' => $request->supplier_id,
                    'transaction_type_id' => $request->transaction_type_id,
                    'updated_by_user_id' => $user->id,
                    'parent_id' => $request->id,
                    'created_by_user_id' => $user->id,
                    'title' => $request->title,
                    'budget' => $request->budget,
                    'enquiry_source_id' => $request->enquiry_source_id,
                    'enquiry_payment_status' =>  $enquiryPaymentStatus,
                    'assigned_to_user_id' => $user->id,
                    'enquiry_status_id' => $request->enquiry_status_id,
                    'customer_name' => $request->customer_name ? $request->customer_name : $parentEnquiry->customer_name,
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
                    'departure_date' => $request->departure_date,
                    'return_date' => $request->return_date,
                    'adult' => $request->adult,
                    'child' => $request->child,
                    'infant' => $request->infant,
                    'class_of_travel' => $request->class_of_travel,
                    'followed_up_at' => $request->followed_up_at,
                    'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $request->id),

                    'status' => $request->status ? $request->status : $parentEnquiry->status,
                ]
            );
        }
        ///EmployeeEvent::dispatch($enquiry);
        // Return success message based on whether it is a create or update
        if ($request->id) {
            return $this->success(new EnquiryResource($enquiry), 'Enquiry updated successfully');
        } else {
            return $this->success(new EnquiryResource($enquiry), 'Enquiry created successfully');
        }
    }


    public function saveAndUpdateMultipleAssignEnquiry(AssignedFlightMultiEnquiryRequest $request)
    {
        //dd($request->all());
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
            if (!$parentEnquiry) {
                return response()->json([
                    'status' => false,
                    'message' => "Enquiry with ID {$request->id} not found"
                ], 404);
            }
            $parentEnquiry->supplier_id = $request->supplier_id;
            $parentEnquiry->transaction_type_id = $request->transaction_type_id;
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
            $parentEnquiry->paid_amount = $request->paid_amount;
            $parentEnquiry->type = $request->type;
            $parentEnquiry->air_line_id = $request->air_line_id;
            $parentEnquiry->package_type = "multi_city";
            $parentEnquiry->from = $request->from;
            $parentEnquiry->to = $request->to;
            $parentEnquiry->departure_date = $request->departure_date;
            $parentEnquiry->return_date = $request->return_date;
            $parentEnquiry->adult = $request->adult;
            $parentEnquiry->child = $request->child;
            $parentEnquiry->infant = $request->infant;
            $parentEnquiry->class_of_travel = $request->class_of_travel;
            $parentEnquiry->status = $request->status ? $request->status : $parentEnquiry->status;
            // $parentEnquiry->created_at =  Carbon::now();
            $parentEnquiry->updated_at =  Carbon::now();

            $parentEnquiry->followed_up_at = $request->followed_up_at ? $request->followed_up_at : $parentEnquiry->followed_up_at;
            $parentEnquiry->save();

            if ($request->packages) {
                foreach ($request->packages as $package) {
                    if (isset($package['id'])) {
                        $parentMultiPackage = Package::findOrFail($package['id']);
                        $parentMultiPackage->update([
                            'enquiry_id' => $parentEnquiry->id,
                            'package_type' => 'multi_city',
                            'departure_date' => $package['departure_date'],
                            'from' => $package['from'],
                            'to' => $package['to'],
                        ]);
                    }
                }
            }
        }


        if ($request->id) {
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
                    'enquiry_payment_status' =>  $enquiryPaymentStatus,
                    'assigned_to_user_id' => $user->id,
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
                    'enquiry_code' => $this->enquiriesRepo->generateEnquiryCode(true, $request->id),
                    'followed_up_at' => $request->followed_up_at,
                ]
            );
            if ($request->packages) {
                foreach ($request->packages as $package) {
                    if (isset($package['id'])) {
                        $parentMultiPackage = Package::findOrFail($package['id']);
                        $childMultiPackage = Package::Create([
                            'parent_id' => $parentMultiPackage->id,
                            'enquiry_id' => $enquiry->id,
                            'package_type' => 'multi_city',
                            'departure_date' => $package['departure_date'],
                            'from' => $package['from'],
                            'to' => $package['to'],
                        ]);
                    }
                }
            }
        }
        // Reload enquiry with packages after creating/updating
        $enquiry->load('packages');
        // Return the enquiry with packages in the response
        if ($request->id) {
            return $this->success(new EnquiryResource($enquiry), 'Enquiry updated Successfully');
        } else {
            return $this->success(new EnquiryResource($enquiry), 'Enquiry Created Successfully');
        }
    }
}
