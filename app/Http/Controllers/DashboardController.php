<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function index() {

        if (Auth::user()->user_role == 'user') {

            $userRelation = Auth::user()->relation; // jisme shift timing hai
            $shiftStart = Carbon::parse($userRelation->shiftTiming->start_time);
            $shiftEnd   = Carbon::parse($userRelation->shiftTiming->end_time);
            $now = Carbon::now();
            $today = Carbon::today();

            $checkInAvailable = $now->between($shiftStart->subHours(2), $shiftEnd);
            $checkOutAvailable = $now->greaterThan($shiftStart) && $now->lessThanOrEqualTo($shiftEnd->addHours(4));
            $attendanceToday = Attendance::where('user_id', Auth::user()->id)->whereDate('date', $today)->first();

            return view('modules.admin.dashboard.index', compact('checkInAvailable', 'checkOutAvailable', 'attendanceToday'));

        } else if (Auth::user()->user_role == 'admin') {
            return view('modules.admin.dashboard.index');
        }
    }
}
