<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\RoleController;
use App\Models\Attendance;
use App\Http\Resources\RoleResource;
use App\Models\Enquiry;
use App\Models\HotelEnquire;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        $authUser = auth()->user();
        $userId = $this->id;

        $enquirySourceCounts = Enquiry::select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $userId)
            ->with('enquirySource')
            ->get()
            ->map(function ($item) {
                return [
                    'enquiry_source_id' => $item->enquiry_source_id,
                    'enquiry_source' => $item,
                    'enquiry_source' => [
                        'enquiry_source' => $item->enquirySource, // Nested relation data
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });

        $enquiryPaymentStatus = Enquiry::select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $userId)
            ->get()
            ->map(function ($item) {
                $statusLabels = [
                    'pending' => 'Pending',
                    'over_paid' => 'Overpaid',
                    'paid' => 'Paid',
                    'not_paid' => 'Not Paid',
                ];
                return [
                    'enquiry_payment_status' => $item->enquiry_payment_status,
                    'label' => $statusLabels[$item->enquiry_payment_status] ?? 'Unknown Status',
                    'enquiryPaymentStatus' => [
                        'enquiryPaymentStatus' => $item, // Nested relation data
                        'count' => $item->count,
                        'label' => $statusLabels[$item->enquiry_payment_status] ?? 'Unknown Status',
                    ],
                    'count' => $item->count,
                ];
            });
        $airLines = Enquiry::select('air_line_id', DB::raw('count(*) as count'))
            ->groupBy('air_line_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $userId)
            ->with('airLine')
            ->get()
            ->map(function ($item) {
                return [
                    'air_line_id' => $item->air_line_id,

                    'airLine' => [
                        'airLine' => $item->airLine, // Nested relation data
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });


        $totalBudget = Enquiry::where('parent_id', null)->where('assigned_to_user_id', $userId)->sum('budget');
        $totalPaidAmount = Enquiry::where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $userId)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

        $hisCreatedEnquiries = Enquiry::where('parent_id', null)->where('created_by_user_id', $userId)->count();
        $assignedEnquiries = Enquiry::where('parent_id', null)->where('assigned_to_user_id', $userId)->count();
        $pendingEnquiries = Enquiry::where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $userId)->count();
        $pendingAmount = Enquiry::where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
        $overPaidAmount = Enquiry::where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $userId)->sum(DB::raw('paid_amount - budget'));
        if ($overPaidAmount > 0) {
            $overPaidAmountSum = Enquiry::where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $userId)->sum(DB::raw('budget'));
            $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
        }
        if ($pendingAmount > 0) {
            $pendingAmountSum = Enquiry::where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
            $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
        }
        $notPaidAmount = Enquiry::where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));

        $flightStatics = [
            'totalBudget' => $totalBudget,
            'totalPaidAmount' => $totalPaidAmount,
            'pendingAmount' => $pendingAmount,
            'notPaidAmount' => $notPaidAmount,
            'overPaidAmount' => $overPaidAmount,
            'hisCreatedEnquiries' => $hisCreatedEnquiries,
            'assigned_enquiries' => $assignedEnquiries,
            'pending_enquiries' => $pendingEnquiries,
            'enquirySourceCount' => $enquirySourceCounts,
            'enquiryPaymentStatus' => $enquiryPaymentStatus,
            'airLines' => $airLines
        ];

        //hotel enquiry

        $hotelEnquirySourceCounts = HotelEnquire::select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $userId)
            ->with('enquirySource')
            ->get()
            ->map(function ($item) {
                return [
                    'enquiry_source_id' => $item->enquiry_source_id,
                    'enquiry_source' => $item,
                    'enquiry_source' => [
                        'enquiry_source' => $item->enquirySource, // Nested relation data
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });

        $hotelEnquiryPaymentStatus = HotelEnquire::select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $userId)
            ->get()
            ->map(function ($item) {
                $statusLabels = [
                    'pending' => 'Pending',
                    'over_paid' => 'Overpaid',
                    'paid' => 'Paid',
                    'not_paid' => 'Not Paid',
                ];
                return [
                    'enquiry_payment_status' => $item->enquiry_payment_status,
                    'label' => $statusLabels[$item->enquiry_payment_status] ?? 'Unknown Status',
                    'enquiryPaymentStatus' => [
                        'enquiryPaymentStatus' => $item, // Nested relation data
                        'count' => $item->count,
                        'label' => $statusLabels[$item->enquiry_payment_status] ?? 'Unknown Status',
                    ],
                    'count' => $item->count,
                ];
            });

        $totalBudgetHotel = HotelEnquire::where('parent_id', null)->where('assigned_to_user_id', $userId)->sum('budget');
        $totalPaidAmountHotel = HotelEnquire::where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $userId)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

        $hisCreatedHotelEnquiries = HotelEnquire::where('parent_id', null)->where('created_by_user_id', $userId)->count();
        $assignedHotelEnquiries = HotelEnquire::where('parent_id', null)->where('assigned_to_user_id', $userId)->count();
        $pendingHotelEnquiries = HotelEnquire::where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $userId)->count();
        $pendingAmountHotel = HotelEnquire::where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
        $overPaidAmountHotel = HotelEnquire::where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $userId)->sum(DB::raw('paid_amount - budget'));
        if ($overPaidAmount > 0) {
            $overPaidAmountSum = HotelEnquire::where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $userId)->sum(DB::raw('budget'));
            $totalPaidAmountHotel = $totalPaidAmount + $overPaidAmountSum;
        }
        if ($pendingAmount > 0) {
            $pendingAmountSum = HotelEnquire::where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
            $totalPaidAmountHotel = $totalPaidAmount + $pendingAmountSum;
        }
        $notPaidAmountHotel = HotelEnquire::where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $userId)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));


        $hotelStatics = [
            'totalBudget' => $totalBudgetHotel,
            'totalPaidAmount' => $totalPaidAmountHotel,
            'pendingAmount' => $pendingAmountHotel,
            'notPaidAmount' => $notPaidAmountHotel,
            'overPaidAmount' => $overPaidAmountHotel,
            'hisCreatedEnquiries' => $hisCreatedHotelEnquiries,
            'assigned_enquiries' => $assignedHotelEnquiries,
            'pending_enquiries' => $pendingHotelEnquiries,
            'enquirySourceCount' => $hotelEnquirySourceCounts,
            'enquiryPaymentStatus' => $hotelEnquiryPaymentStatus,
        ];





        if (in_array($authUser->role_id, [1, 4])) { // Admin and Super Admin roles
            return [
                'id' => $this->id,
                'role_id' => (int) $this->role_id,
                'role' => new RoleResource($this->whenLoaded('role')),
                'department_id' => $this->department_id,
                'department' => new DepartmentResource(($this->whenLoaded('department'))),
                'branch_id' => $this->branch_id,
                'branch' => new BranchResource(($this->whenLoaded('branch'))),
                'age' => $this->age,
                'salary' => $this->salary,
                'name' => $this->name,
                'email' => $this->email,
                'image' => $this->image,
                'password' => $this->password,
                'full_path' => $this->full_path,
                'phone_no' => $this->phone_no,
                'is_verified' => $this->is_verified,
                'description' => $this->description,
                'employee_verified_at' => $this->employee_verified_at,
                'status' => $this->status,
                'plain_text_token' => $this->when($this->plain_text_token, $this->plain_text_token),
                'enquiries' => EnquiryResource::collection($this->whenLoaded('enquiries')),
                'flightStatics' => $flightStatics,
                'hotelStatics' => $hotelStatics,
                'user_login' => $this->user_login,
                'last_seen_at' => $this->last_seen_at,
                'sessions' => $this->whenLoaded('tokens', function () use ($request) {
                    return $this->tokens->map(function ($token) use ($request) {
                        return [
                            'device' => $token->name,
                            'last_seen_at' => $token->last_seen_at,
                            'created_at' => $token->created_at,
                            'token' => $token->token,
                            'is_current' => $request->user()?->currentAccessToken()?->id === $token->id,
                        ];
                    });
                }),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }

        // Limited data for employees or other roles
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_no' => $this->phone_no,
            'image' => $this->image,
            'full_path' => $this->full_path,
            'role_id' => $this->role_id,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource(($this->whenLoaded('branch'))),
            'department_id' => $this->department_id,
            'role' => new RoleResource($this->whenLoaded('role')),
            'department' => new DepartmentResource(($this->whenLoaded('department'))),
            // 'enquiries' => EnquiryResource::collection($this->whenLoaded('enquiries')),
            'user_login' => $this->user_login,
            'last_seen_at' => $this->last_seen_at,
            'sessions' => $this->whenLoaded('tokens', function () use ($request) {
                return $this->tokens->map(function ($token) use ($request) {
                    return [
                        'device' => $token->name,
                        'last_seen_at' => $token->last_seen_at,
                        'created_at' => $token->created_at,
                        'token' => $token->token,
                        'is_current' => $request->user()?->currentAccessToken()?->id === $token->id,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
