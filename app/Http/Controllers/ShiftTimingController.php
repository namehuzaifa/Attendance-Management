<?php

namespace App\Http\Controllers;

use App\Models\ShiftTiming;
use Illuminate\Http\Request;

class ShiftTimingController extends Controller
{
    public function index() {
        $shiftTiming = ShiftTiming::all();
        return view('modules.admin.shiftTiming.list', compact('shiftTiming'));
    }

    public function store(Request $r) {
        $r->validate([
            'name'=>'required|string',
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i',
            'grace_period'=>'nullable|integer|min:0'
        ]);

        ShiftTiming::create($r->only('name','start_time','end_time','grace_period'));

        return response()->json(['status' => true, 'message' => "shift added successfully"]);

    }

    public function update(Request $r, $id) {
        $r->validate([
            'name'=>'required|string',
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i',
            'grace_period'=>'nullable|integer|min:0'
        ]);

        $s = ShiftTiming::findOrFail($id);
        $s->update($r->only('name','start_time','end_time','grace_period'));
        return $s;
    }

    public function destroy(Request $r) {
        ShiftTiming::findOrFail($r->id)->delete();
        return response()->json(['status' => true, 'message' => "shift Deleted successfully"]);
    }
}
