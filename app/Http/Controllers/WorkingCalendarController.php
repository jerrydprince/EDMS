<?php

namespace App\Http\Controllers;

use App\Models\WorkflowHoliday;
use App\Models\WorkflowWorkingDay;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkingCalendarController extends Controller
{
    public function index()
    {
        $workingDays = WorkflowWorkingDay::orderBy('day_of_week')->get();
        if ($workingDays->isEmpty()) {
            // Seed defaults if empty
            $days = [
                1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
            ];
            foreach ($days as $num => $name) {
                WorkflowWorkingDay::create([
                    'day_of_week' => $num,
                    'day_name' => $name,
                    'is_working_day' => in_array($num, [1, 2, 3, 4, 5]),
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00'
                ]);
            }
            $workingDays = WorkflowWorkingDay::orderBy('day_of_week')->get();
        }

        $holidays = WorkflowHoliday::orderBy('holiday_date', 'desc')->get();

        return Inertia::render('admin/WorkingCalendarSetup', [
            'workingDays' => $workingDays,
            'holidays' => $holidays
        ]);
    }

    public function updateWorkingDays(Request $request)
    {
        $request->validate([
            'days' => 'required|array',
            'days.*.id' => 'required|exists:workflow_working_days,id',
            'days.*.is_working_day' => 'required|boolean',
            'days.*.start_time' => 'required|date_format:H:i:s',
            'days.*.end_time' => 'required|date_format:H:i:s',
        ]);

        foreach ($request->days as $dayData) {
            WorkflowWorkingDay::where('id', $dayData['id'])->update([
                'is_working_day' => $dayData['is_working_day'],
                'start_time' => $dayData['start_time'],
                'end_time' => $dayData['end_time'],
            ]);
        }

        return redirect()->back()->with('success', 'Working days updated successfully.');
    }

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required|date',
        ]);

        WorkflowHoliday::create([
            'name' => $request->name,
            'holiday_date' => $request->holiday_date,
        ]);

        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    public function deleteHoliday($id)
    {
        $holiday = WorkflowHoliday::findOrFail($id);
        $holiday->delete();
        
        return redirect()->back()->with('success', 'Holiday removed successfully.');
    }
}
