<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\OtherService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class StaticsOfOtherServicesController extends Controller
{
    public function otherServiceEnquiresList(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $startDate = $request->start_date
        ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        : ($request->end_date
            ? Carbon::createFromFormat('Y-m-d', $request->end_date)->startOfYear()->year(2023)
            : now()->startOfYear()->year(2024));

        $endDate = $request->end_date
            ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
            : ($request->start_date
                ? now()->endOfDay()
                : now());
       
        if ($startDate->greaterThan($endDate)) {
                return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }
        
        if ($startDate->greaterThan($endDate)) {
                    return response()->json(['error' => 'Start date cannot be after end date'], 422);
                }

        $user = auth()->user();


        // Initialize query builder for filtering

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $totalOtherServiceEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();

        $transactionType = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->where('parent_id', null)
            ->with('transactionType')
            ->get()
            ->map(function ($item) {
                return [
                    'transaction_type_id' => $item->transaction_type_id,

                    'transactionType' => [
                        'transactionType' => $item->transactionType,
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });


        $enquirySourceCounts = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
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

        $enquiryPaymentStatus = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
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

        if (!in_array($user->role_id, [1, 4])) {
            OtherService::whereBetween('created_at', [$startDate, $endDate])->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)
                    ->orWhere('assigned_to_user_id', $user->id);
            });

            $hisCreatedHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalOtherServiceEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            return response()->json([
                // 'totalAdmins' => $totalAdmins,
                // 'totalSuperAdmins' => $totalSuperAdmins,
                // 'totalHotelEnquiries' => $totalHotelEnquiries,
                'totalOtherServiceEnquiries' => $totalOtherServiceEnquiries,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'pending_enquiries' => $pendingEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
            ]);
        } else {
            // For admins and superadmins, show statistics for all employees
            $totalBudget = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->sum('price_quote');
            $totalPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'paid')->sum('paid_amount');
            $hisCreatedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalOtherServiceEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingOtherServiceEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            $lastMonthEarnings = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $totalEmployees = User::where('role_id', 3)->count();
            $totalUsers = User::where('role_id', 2)->count();
            $pendingAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->whereColumn('paid_amount', '<', 'price_quote')
                ->sum(DB::raw('price_quote - paid_amount'));
            $overPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'over_paid')->whereColumn('price_quote', '<', 'paid_amount')
                ->sum(DB::raw('paid_amount - price_quote'));
            $notPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('price_quote - paid_amount'));

            if ($overPaidAmount > 0) {
                $overPaidAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('price_quote'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }


            return response()->json([
                'totalAdmins' => $totalAdmins,
                'totalSuperAdmins' => $totalSuperAdmins,
                'totalBudget' => $totalBudget,
                'totalPaidAmount' => $totalPaidAmount,
                'pendingAmount' => $pendingAmount ?? 0,
                'overPaidAmount' => $overPaidAmount ?? 0,
                'notPaidAmount' => $notPaidAmount ?? 0,
                'totalEmployees' => $totalEmployees,
                'totalUsers' => $totalUsers,
                // 'lastMonthEarnings' => $lastMonthEarnings,
                'totalOtherServiceEnquiries' => $totalOtherServiceEnquiries,
                'pendingOtherServiceEnquiries' => $pendingOtherServiceEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'transactionType' => $transactionType

            ]);
        }
    }

    public function profileStatics(Request $request)
    {

        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);


        $startDate = $request->start_date
        ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        : ($request->end_date
            ? Carbon::createFromFormat('Y-m-d', $request->end_date)->startOfYear()->year(2023)
            : now()->startOfYear()->year(2024));

        $endDate = $request->end_date
            ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
            : ($request->start_date
                ? now()->endOfDay()
                : now());
       
        if ($startDate->greaterThan($endDate)) {
                return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }
        
        if ($startDate->greaterThan($endDate)) {
                    return response()->json(['error' => 'Start date cannot be after end date'], 422);
                }

        $enquiriesQuery = OtherService::whereBetween('created_at', [$startDate, $endDate]);

        $user = auth()->user();

        $user = auth()->user();

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $OtherEnquirySourceCounts = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $user->id)
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

        $otherEnquiryPaymentStatus = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $user->id)
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

            $transactionType = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->where('parent_id', null)
            ->with('transactionType')
            ->where('assigned_to_user_id', $user->id)
            ->get()
            ->map(function ($item) {
                return [
                    'transaction_type_id' => $item->transaction_type_id,

                    'transactionType' => [
                        'transactionType' => $item->transactionType,
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });



        if (!in_array($user->role_id, [1, 4])) {
            // $totalAmount = Enquiry::where('parent_id', null)->where('assigned_to_user_id', $user->id)->sum('paid_amount');
            $hisCreatedHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $bookingHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereNotNull('booking_reference_no')->where('created_by_user_id', $user->id)->count();
            $assignedHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingHotelEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();


            return response()->json([
                'hisCreatedEnquiries' => $hisCreatedHotelEnquiries,
                'assigned_enquiries' => $assignedHotelEnquiries,
                'pending_enquiries' => $pendingHotelEnquiries,
                'enquirySourceCount' => $OtherEnquirySourceCounts,
                'enquiryPaymentStatus' => $otherEnquiryPaymentStatus,
            ]);
        } else {
            $totalBudget = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->sum('price_quote');
            $totalPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '=', 'paid_amount')->where('assigned_to_user_id', $user->id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $assignedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();
            $lastMonthEarnings = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('price_quote - paid_amount'));
            $overPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('paid_amount - price_quote'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('price_quote'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('price_quote - paid_amount'));


            return response()->json([
                'totalAdmins' => $totalAdmins,
                'totalSuperAdmins' => $totalSuperAdmins,
                'totalBudget' => $totalBudget,
                'totalPaidAmount' => $totalPaidAmount,
                'pendingAmount' => $pendingAmount,
                'overPaidAmount' => $overPaidAmount,
                'notPaidAmount' => $notPaidAmount,
                'hisCreatedEnquiries' => $hisCreatedEnquiries,
                'assigned_enquiries' => $assignedEnquiries,
                'pending_enquiries' => $pendingEnquiries,
                // 'lastMonthEarnings' => $lastMonthEarnings,
                'enquirySourceCount' => $OtherEnquirySourceCounts,
                'enquiryPaymentStatus' => $otherEnquiryPaymentStatus,
                'transactionType' => $transactionType
            ]);
        }
    }
}
