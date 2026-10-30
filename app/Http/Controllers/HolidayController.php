<?php

namespace App\Http\Controllers;

use App\Models\OffDay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Public holidays. The off_days table shipped with nothing but an id, so a
 * holiday could not be recorded anywhere and every one of them was reported
 * as an absence.
 */
class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $holidays = OffDay::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return view('modules.admin.holiday.list', [
            'holidays' => $holidays,
            'year'     => $year,
            'years'    => range(now()->year - 2, now()->year + 2),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:120'],
            'date'         => ['required', 'date', Rule::unique('off_days', 'date')],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['nullable', 'boolean'],
        ]);

        OffDay::create([
            'title'        => $request->title,
            'date'         => $request->date,
            'description'  => $request->description,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return response()->json(['status' => true, 'message' => 'Holiday added successfully']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:120'],
            'date'         => ['required', 'date', Rule::unique('off_days', 'date')->ignore($id)],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['nullable', 'boolean'],
        ]);

        OffDay::findOrFail($id)->update([
            'title'        => $request->title,
            'date'         => $request->date,
            'description'  => $request->description,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return response()->json(['status' => true, 'message' => 'Holiday updated successfully']);
    }

    public function destroy(Request $request)
    {
        OffDay::findOrFail($request->id)->delete();

        return response()->json(['status' => true, 'message' => 'Holiday deleted successfully']);
    }
}
