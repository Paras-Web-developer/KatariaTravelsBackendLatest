<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repositories\AttendanceRepository;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends BaseController
{

    protected $attendanceRepo;

    public function __construct(AttendanceRepository $attendanceRepo)
    {
        $this->attendanceRepo = $attendanceRepo;
    }

    public function list(Request $request)
    {
        $authUser = auth()->user();

        if (in_array($authUser->role_id, [1, 3])) {
            $limit = $request->has('limit') ? $request->limit : 5;
            $response = Attendance::latest()->with('employeeUser')->paginate($request->get('limit', config('app.pagination_limit')))->withQueryString();
            return $this->successWithPaginateData(AttendanceResource::collection($response), $response);
        } else {
            $limit = $request->has('limit') ? $request->limit : 5;
            $response = Attendance::latest()->where('employee_user_id', $authUser->id)->with('employeeUser')->paginate($request->get('limit', config('app.pagination_limit')))->withQueryString();
            return $this->successWithPaginateData(AttendanceResource::collection($response), $response);
        }
    }


    public function markAttendance(Request $request)
    {
        $user = auth()->user();
        $today = now()->toDateString();

        // Retrieve today's attendance records
        $attendancesToday = Attendance::where('employee_user_id', $user->id)
            ->whereDate('date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $lastAttendance = $attendancesToday->first();

        if (!$lastAttendance || $lastAttendance->exit_time) {
            // If no attendance exists for today or the last record has an exit time, create a new entry
            Attendance::create([
                'employee_user_id' => $user->id,
                'date' => $today,
                'entry_time' => now()->toTimeString(),
                'status' => 'present',
            ]);
            return response()->json(['message' => 'Entry time marked.']);
        } else {
            // Calculate the time difference between entry and exit times
            $entryTime = \Carbon\Carbon::parse($lastAttendance->entry_time);
            $exitTime = now();
            $totalDuration = $exitTime->diff($entryTime)->format('%H:%I:%S');

            // Update with exit time and calculated total time
            $lastAttendance->update([
                'exit_time' => $exitTime->toTimeString(),
                'total_time' => $totalDuration,
            ]);

            return response()->json([
                'message' => 'Exit time marked, total time updated.',
                'total_time' => $totalDuration,
            ]);
        }
    }




    public function getMonthlyAttendance(Request $request, $month)
    {
        $user = auth()->user();
        $monthFormat = Carbon::parse($month)->format('m');
        $yearFormat = Carbon::parse($month)->format('Y');

        $attendances = Attendance::where('employee_user_id', $user->id)
            ->whereMonth('date', $monthFormat)
            ->whereYear('date', $yearFormat)
            ->where('status', 'present')
            ->get();
      

        $totalDays = $attendances->count();
        $totalHours = $attendances->reduce(function ($carry, $attendance) {
            if ($attendance->daily_duration) {
                $duration = Carbon::parse($attendance->daily_duration);
                return $carry + $duration->hour + ($duration->minute / 60);
            }
            return $carry;
        }, 0);

        return response()->json([
            'total_days' => $totalDays,
            'total_hours' => round($totalHours, 2),
        ]);
    }
}
