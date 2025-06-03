<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\HotelEnquire;
use App\Models\OtherService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class EmployeeStaticController extends Controller
{
    public function emoloyeFlightStaticsList(Request $request , $id)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $employee = User::find($id);
        
        if($employee==null){
            return response()->json(['error' => 'Record Not Fund'], 422);
 
        }

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

        $enquiriesQuery = Enquiry::whereBetween('created_at', [$startDate, $endDate]);


        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $totalEnquiries = $enquiriesQuery->where('parent_id', null)->count();

        $user = auth()->user();
        $enquiriesQuery = Enquiry::query();
        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $enquirySourceCounts = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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

        $enquiryPaymentStatus = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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
        $airLine = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('air_line_id', DB::raw('count(*) as count'))
            ->groupBy('air_line_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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

        $suppliers = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('supplier_id', DB::raw('count(*) as count'))
            ->groupBy('supplier_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
            ->with('supplier')
            ->get()
            ->map(function ($item) {
                return [
                    'supplier_id' => $item->supplier_id,

                    'supplier' => [
                        'supplier' => $item->supplier,
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });



        $transactionType = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->where('parent_id', null)
            ->with('transactionType')
            ->where('assigned_to_user_id', $id)
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



        
            $totalBudget = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->sum('budget');
            $totalPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $id)->count();
            $bookingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereNotNull('booking_reference')->where('created_by_user_id', $id)->count();
            $assignedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->count();
            $pendingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $id)->count();
            $lastMonthEarnings = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
            $overPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('paid_amount - budget'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));


            return response()->json([
                'message' => 'flight bookings Data fetched successfully',

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
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'airLines' => $airLine,
                'transactionType' => $transactionType,
                'suppliers' => $suppliers,
            ]);
        
    }

    public function emoloyeHotelStaticsList(Request $request , $id)
    {

        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);
        
        $employee = User::find($id);
        
        if($employee==null){
            return response()->json(['error' => 'Record Not Fund'], 422);
 
        }
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

        $enquiriesQuery = HotelEnquire::whereBetween('created_at', [$startDate, $endDate]);
        $user = auth()->user();

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $hotelEnquirySourceCounts = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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

        $transactionType = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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


        $hotelEnquiryPaymentStatus = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
            ->groupBy('enquiry_payment_status')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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


      
            $totalBudget = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->sum('budget');
            $totalPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $id)->count();
            $assignedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->count();
            $pendingEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $id)->count();
            $lastMonthEarnings = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
            $overPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('paid_amount - budget'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));


            return response()->json([
                'message' => 'hotel static Data fetched successfully',

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
                'enquirySourceCount' => $hotelEnquirySourceCounts,
                'enquiryPaymentStatus' => $hotelEnquiryPaymentStatus,
                'transactionType' => $transactionType
            ]);
        
    }

    public function emoloyeOtherServiceStaticsList(Request $request , $id)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $employee = User::find($id);
        
        if($employee==null){
            return response()->json(['error' => 'Record Not Fund'], 422);
        }

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

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $OtherEnquirySourceCounts = OtherService::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
            ->groupBy('enquiry_source_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $id)
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
            ->where('assigned_to_user_id', $id)
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
            ->where('assigned_to_user_id', $id)
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



       
            $totalBudget = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->sum('price_quote');
            $totalPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '=', 'paid_amount')->where('assigned_to_user_id', $id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $id)->count();
            $assignedEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->count();
            $pendingEnquiries = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $id)->count();
            $lastMonthEarnings = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('price_quote - paid_amount'));
            $overPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('paid_amount - price_quote'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('price_quote', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $id)->sum(DB::raw('price_quote'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = OtherService::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $id)->whereColumn('paid_amount', '<', 'price_quote')->sum(DB::raw('price_quote - paid_amount'));


            return response()->json([
                'message' => 'other Services Data fetched successfully',
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
