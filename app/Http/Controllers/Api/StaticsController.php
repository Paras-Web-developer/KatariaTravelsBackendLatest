<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\HotelEnquire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class StaticsController extends Controller
{
    public function list(Request $request)
    {
        $user = auth()->user();
       
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);
        // Determine the date range for filtering.
        // $startDate = $request->start_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        //     : now()->startOfMonth();
        // $endDate = $request->end_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
        //     : now()->endOfMonth();
      

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
       // dd($startDate, $endDate);
        if ($startDate->greaterThan($endDate)) {
            return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }

        // Ensure start_date is not after end_date
        if ($startDate->greaterThan($endDate)) {
            return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }
        // Initialize the enquiry query builder to filter by the date range.
        $enquiriesQuery = Enquiry::whereBetween('created_at', [$startDate, $endDate]);

        //$enquiriesQuery = $enquiriesQuery->whereBetween('created_at', [$startDate, $endDate]);

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $totalEnquiries = $enquiriesQuery->where('parent_id', null)->count();
        // Group by enquiry source and count enquiries
        $enquirySourceCounts = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
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


        $enquiryPaymentStatus = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
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


        $transactionType = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
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


        $airLine = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('air_line_id', DB::raw('count(*) as count'))
            ->groupBy('air_line_id')
            ->where('parent_id', null)
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
            ->with('supplier')
            ->get()
            ->map(function ($item) {
                return [
                    'supplier_id' => $item->supplier_id,

                    'supplier' => [
                        'supplier' => $item->supplier, // Nested relation data
                        'count' => $item->count,
                    ],
                    'count' => $item->count,
                ];
            });


        // If the user is not an admin or superadmin, show only their own statistics
        if (!in_array($user->role_id, [1, 4])) {
            Enquiry::whereBetween('created_at', [$startDate, $endDate])->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)
                    ->orWhere('assigned_to_user_id', $user->id);
            });

            $hisCreatedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            // $lastMonthEarnings =Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereBetween('created_at', [
            //     now()->subMonth()->startOfMonth(),
            //     now()->subMonth()->endOfMonth(),
            // ])->where('assigned_to_user_id', $user->id)->sum('paid_amount');
            // $pendingAmount =Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('paid_amount', '<', 'budget')->where('assigned_to_user_id', $user->id)
            //     ->sum(DB::raw('budget - paid_amount'));
            return response()->json([
                // 'lastMonthEarnings' => $lastMonthEarnings,
                // 'totalAdmins' => $totalAdmins,
                // 'totalSuperAdmins' => $totalSuperAdmins,
                'totalEnquiries' => $totalEnquiries,
                // 'hisCreatedEnquiries' => $hisCreatedEnquiries,
                // 'assigned_enquiries' => $assignedEnquiries,
                'pending_enquiries' => $pendingEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'airLines' => $airLine
            ]);
        } else {
            // For admins and superadmins, show statistics for all employees
            $totalBudget = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->sum('budget');
            $totalPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'paid')->sum('paid_amount');
            $hisCreatedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            $lastMonthEarnings = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $totalEmployees = User::where('role_id', 3)->count();
            $totalUsers = User::where('role_id', 2)->count();
            $pendingAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->whereColumn('paid_amount', '<', 'budget')
                ->sum(DB::raw('budget - paid_amount'));

            $overPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'over_paid')->whereColumn('budget', '<', 'paid_amount')
                ->sum(DB::raw('paid_amount - budget'));
            $notPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));

            if ($overPaidAmount > 0) {
                $overPaidAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $adminPaymentStatus = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('admin_payment_status', 'pending')->count();


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
                'totalEnquiries' => $totalEnquiries,
                // 'hisCreatedEnquiries' => $hisCreatedEnquiries,
                // 'assigned_enquiries' => $assignedEnquiries,
                'pending_enquiries' => $pendingEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'airLines' => $airLine,
                'transactionType' => $transactionType,
                'adminPaymentStatus' => $adminPaymentStatus,
                'suppliers' => $suppliers,
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
       // dd($startDate, $endDate);
        if ($startDate->greaterThan($endDate)) {
                return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }
        
        if ($startDate->greaterThan($endDate)) {
                    return response()->json(['error' => 'Start date cannot be after end date'], 422);
                }

        // $startDate = $request->start_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        //     : now()->startOfMonth();
        // $endDate = $request->end_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
        //     : now()->endOfMonth();

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

        $enquiryPaymentStatus = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
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
        $airLine = Enquiry::whereBetween('created_at', [$startDate, $endDate])->select('air_line_id', DB::raw('count(*) as count'))
            ->groupBy('air_line_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $user->id)
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
            ->where('assigned_to_user_id', $user->id)
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
            $hisCreatedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $bookingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereNotNull('booking_reference')->where('created_by_user_id', $user->id)->count();
            $assignedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();


            return response()->json([
                // 'totalAdmins' => $totalAdmins,
                // 'totalSuperAdmins' => $totalSuperAdmins,
                'hisCreatedEnquiries' => $hisCreatedEnquiries,
                'assigned_enquiries' => $assignedEnquiries,
                'pending_enquiries' => $pendingEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'airLines' => $airLine,
                'suppliers' => $suppliers,
            ]);
        } else {
            $totalBudget = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->sum('budget');
            $totalPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $user->id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $bookingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereNotNull('booking_reference')->where('created_by_user_id', $user->id)->count();
            $assignedEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();
            $lastMonthEarnings = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
            $overPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('paid_amount - budget'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = Enquiry::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));


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
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'airLines' => $airLine,
                'transactionType' => $transactionType,
                'suppliers' => $suppliers,
            ]);
        }
    }

    public function hotelEnquiresList(Request $request)
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
       // dd($startDate, $endDate);
        if ($startDate->greaterThan($endDate)) {
            return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }
        
        if ($startDate->greaterThan($endDate)) {
            return response()->json(['error' => 'Start date cannot be after end date'], 422);
        }


        // $startDate = $request->start_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        //     : now()->startOfMonth();
        // $endDate = $request->end_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
        //     : now()->endOfMonth();

        $enquiriesQuery = HotelEnquire::whereBetween('created_at', [$startDate, $endDate]);

        $user = auth()->user();


        // Initialize query builder for filtering

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $totalHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();

        $enquirySourceCounts = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
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

        $transactionType = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
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


        $enquiryPaymentStatus = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_payment_status', DB::raw('count(*) as count'))
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
            HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)
                    ->orWhere('assigned_to_user_id', $user->id);
            });

            $hisCreatedHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            return response()->json([
                // 'totalAdmins' => $totalAdmins,
                // 'totalSuperAdmins' => $totalSuperAdmins,
                // 'totalHotelEnquiries' => $totalHotelEnquiries,
                'totalHotelEnquiries' => $totalHotelEnquiries,
                'enquirySourceCounts' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'pending_enquiries' => $pendingEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
            ]);
        } else {
            // For admins and superadmins, show statistics for all employees
            $totalBudget = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->sum('budget');
            $totalPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'paid')->sum('paid_amount');
            $hisCreatedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $totalHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->count();
            $assignedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->count();
            $lastMonthEarnings = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $totalEmployees = User::where('role_id', 3)->count();
            $totalUsers = User::where('role_id', 2)->count();
            $pendingAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->whereColumn('paid_amount', '<', 'budget')
                ->sum(DB::raw('budget - paid_amount'));
            $overPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'over_paid')->whereColumn('budget', '<', 'paid_amount')
                ->sum(DB::raw('paid_amount - budget'));
            $notPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));

            if ($overPaidAmount > 0) {
                $overPaidAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
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
                'totalHotelEnquiries' => $totalHotelEnquiries,
                'pendingHotelEnquiries' => $pendingHotelEnquiries,
                'enquirySourceCount' => $enquirySourceCounts,
                'enquiryPaymentStatus' => $enquiryPaymentStatus,
                'transactionType' => $transactionType,
            ]);
        }
    }

    public function profileStaticsHotel(Request $request)
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


        // $startDate = $request->start_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
        //     : now()->startOfMonth();
        // $endDate = $request->end_date
        //     ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
        //     : now()->endOfMonth();

        $enquiriesQuery = HotelEnquire::whereBetween('created_at', [$startDate, $endDate]);

        $user = auth()->user();

        $user = auth()->user();

        $totalAdmins = User::where('role_id', 1)->count();
        $totalSuperAdmins = User::where('role_id', 4)->count();
        $hotelEnquirySourceCounts = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('enquiry_source_id', DB::raw('count(*) as count'))
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

        $transactionType = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->select('transaction_type_id', DB::raw('count(*) as count'))
            ->groupBy('transaction_type_id')
            ->where('parent_id', null)
            ->where('assigned_to_user_id', $user->id)
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


        if (!in_array($user->role_id, [1, 4])) {
            // $totalAmount = Enquiry::where('parent_id', null)->where('assigned_to_user_id', $user->id)->sum('paid_amount');
            $hisCreatedHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $bookingHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereNotNull('booking_reference')->where('created_by_user_id', $user->id)->count();
            $assignedHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingHotelEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();


            return response()->json([
                // 'totalAdmins' => $totalAdmins,
                // 'totalSuperAdmins' => $totalSuperAdmins,
                'hisCreatedEnquiries' => $hisCreatedHotelEnquiries,
                'assigned_enquiries' => $assignedHotelEnquiries,
                'pending_enquiries' => $pendingHotelEnquiries,
                'enquirySourceCount' => $hotelEnquirySourceCounts,
                'enquiryPaymentStatus' => $hotelEnquiryPaymentStatus,
            ]);
        } else {
            $totalBudget = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->sum('budget');
            $totalPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '=', 'paid_amount')->where('assigned_to_user_id', $user->id)->where('enquiry_payment_status', 'paid')->sum('paid_amount');

            $hisCreatedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('created_by_user_id', $user->id)->count();
            $assignedEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->count();
            $pendingEnquiries = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('status', 'pending')->where('assigned_to_user_id', $user->id)->count();
            $lastMonthEarnings = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('assigned_to_user_id', $user->id)->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])->sum('paid_amount');
            $pendingAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));
            $overPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('paid_amount - budget'));
            if ($overPaidAmount > 0) {
                $overPaidAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->whereColumn('budget', '<', 'paid_amount')->where('enquiry_payment_status', 'over_paid')->where('assigned_to_user_id', $user->id)->sum(DB::raw('budget'));
                $totalPaidAmount = $totalPaidAmount + $overPaidAmountSum;
            }
            if ($pendingAmount > 0) {
                $pendingAmountSum = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'pending')->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('paid_amount'));
                $totalPaidAmount = $totalPaidAmount + $pendingAmountSum;
            }
            $notPaidAmount = HotelEnquire::whereBetween('created_at', [$startDate, $endDate])->where('parent_id', null)->where('enquiry_payment_status', 'not_paid')->where('paid_amount', 0)->where('assigned_to_user_id', $user->id)->whereColumn('paid_amount', '<', 'budget')->sum(DB::raw('budget - paid_amount'));


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
                'enquirySourceCount' => $hotelEnquirySourceCounts,
                'enquiryPaymentStatus' => $hotelEnquiryPaymentStatus,
                'transactionType' => $transactionType
            ]);
        }
    }
}
