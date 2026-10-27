<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\Certificate;
use App\Models\KRA;
use App\Models\TaskAssignment;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PMSController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $roles = $user->roles ? $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray() : [];
        $isExecutive = count(array_intersect($roles, ['super admin', 'super user', 'admin', 'accountant-general', 'agf', 'agf registry'])) > 0
            || str_contains(strtolower($user->email ?? ''), 'agf')
            || str_contains(strtolower($user->designation ?? ''), 'accountant-general');

        if ($isExecutive) {
            $departments = Department::orderBy('title', 'ASC')->get();
            $firstDept = $departments->first();
            $users = $firstDept 
                ? User::with(['roles', 'unit', 'division', 'department'])->where('department_id', $firstDept->id)->get() 
                : [];
        } elseif (in_array('director', $roles) || $user->is_department_head) {
            $departments = Department::where('id', $user->department_id)->get();
            $users = User::with(['roles', 'unit', 'division', 'department'])->where('department_id', $user->department_id)->get();
        } elseif (in_array('deputy director', $roles) || $user->is_division_head) {
            $departments = Department::where('id', $user->department_id)->get();
            $users = User::with(['roles', 'unit', 'division', 'department'])->where('division_id', $user->division_id)->get();
        } elseif (in_array('assistant director', $roles) || $user->is_unit_head) {
            $departments = Department::where('id', $user->department_id)->get();
            $users = User::with(['roles', 'unit', 'division', 'department'])->where('unit_id', $user->unit_id)->get();
        } else {
            // Regular user: redirect directly to their own profile scorecard
            return redirect('/dashboard/pms-current-user-profile');
        }

        return inertia('PMS/index', compact('departments', 'users'));
    }

    public function overview()
    {
        return inertia('PMS/Overview');
    }

    public function settings()
    {
        return inertia('PMS/Settings');
    }

    public function getDepartmentUsers(Request $request)
    {
        $deptId = $request->id;
        $users = User::with(['roles', 'unit', 'division', 'department'])
            ->where('department_id', $deptId)
            ->get();
        return response()->json($users);
    }

    public function completionRate($taskAssignments)
    {
        if (!$taskAssignments || $taskAssignments->isEmpty()) {
            return [
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'uncompleted_tasks' => 0,
                'pending_tasks' => 0,
                'completion_rate' => 0,
                'uncompleted_rate' => 0,
                'pending_rate' => 0,
                'avg_completion_time' => 'N/A',
                'on_time_rate' => 0,
            ];
        }

        $total = $taskAssignments->count();
        $completed = $taskAssignments->filter(fn($a) => in_array(strtolower($a->status ?? ''), ['completed', 'approved']));
        $pending = $taskAssignments->filter(fn($a) => in_array(strtolower($a->status ?? ''), ['pending', 'in_progress', 'under_review', 'under review', 'active']));
        $uncompleted = $taskAssignments->filter(fn($a) => !in_array(strtolower($a->status ?? ''), ['completed', 'approved', 'pending', 'in_progress', 'under_review', 'under review', 'active']));

        $completedCount = $completed->count();
        $pendingCount = $pending->count();
        $uncompletedCount = $uncompleted->count();

        $completionRate = $total > 0 ? round(($completedCount / $total) * 100, 1) : 0;
        $pendingRate = $total > 0 ? round(($pendingCount / $total) * 100, 1) : 0;
        $uncompletedRate = $total > 0 ? round(($uncompletedCount / $total) * 100, 1) : 0;

        // Calculate turnaround time for completed assignments
        $turnaroundMinutes = [];
        $onTimeCount = 0;
        foreach ($completed as $item) {
            $submission = $item->taskSubmissions ? $item->taskSubmissions->first() : null;
            $completedAt = $submission ? $submission->created_at : $item->updated_at;
            if ($completedAt && $item->created_at) {
                $diff = $item->created_at->diffInMinutes($completedAt);
                if ($diff > 0) {
                    $turnaroundMinutes[] = $diff;
                }
            }
            if ($item->due_at && $completedAt && $completedAt->lte($item->due_at)) {
                $onTimeCount++;
            }
        }

        $avgMinutes = count($turnaroundMinutes) > 0 ? round(array_sum($turnaroundMinutes) / count($turnaroundMinutes)) : 0;
        $avgTimeString = 'N/A';
        if ($avgMinutes > 0) {
            $hours = floor($avgMinutes / 60);
            $mins = $avgMinutes % 60;
            if ($hours >= 24) {
                $days = floor($hours / 24);
                $remHours = $hours % 24;
                $avgTimeString = "{$days}d {$remHours}h";
            } elseif ($hours > 0) {
                $avgTimeString = "{$hours}h {$mins}m";
            } else {
                $avgTimeString = "{$mins} mins";
            }
        }

        $onTimeRate = $completedCount > 0 ? round(($onTimeCount / $completedCount) * 100, 1) : 100;

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completedCount,
            'uncompleted_tasks' => $uncompletedCount,
            'pending_tasks' => $pendingCount,
            'completion_rate' => $completionRate,
            'uncompleted_rate' => $uncompletedRate,
            'pending_rate' => $pendingRate,
            'avg_completion_time' => $avgTimeString,
            'on_time_rate' => $onTimeRate,
        ];
    }

    public function viewPMSUserProfile($id)
    {
        $user = User::with([
            'unit',
            'division',
            'department',
            'roles',
            'certificates',
            'leave' => function($query) {
                $query->with(['reliefUser', 'approver'])->latest();
            },
            'misconducts' => function($query) {
                $query->with('reporter')->latest();
            },
            'receivedQueries' => function($query) {
                $query->with(['issuer', 'adjudicator'])->latest();
            },
            'rating',
            'attendance' => function($query) {
                $query->orderBy('created_at', 'DESC');
            },
            'taskAssignments' => function($query) {
                $query->with(['task', 'assignedBy', 'taskSubmissions'])->latest();
            }
        ])->find($id);

        if (!$user) {
            abort(404, 'User profile not found');
        }

        $taskAssignments = $user->taskAssignments ?: collect([]);
        $ratings = $user->rating ? $user->rating->groupBy('key') : collect([]);
        $taskCompletionRate = $this->completionRate($taskAssignments);

        // Transform task assignments to match Tasks.vue expected structure
        $tasks = $taskAssignments->map(function($a) {
            return (object)[
                'id' => $a->task ? $a->task->id : $a->id,
                'title' => $a->task ? $a->task->title : ($a->description ?: 'Assigned Task'),
                'description' => $a->description ?: ($a->task ? $a->task->description : ''),
                'status' => $a->status ?: 'Pending',
                'mail_id' => $a->task ? $a->task->mail_id : null,
                'file_id' => $a->task ? $a->task->file_id : null,
                'assigned_by' => $a->assignedBy,
                'comments_count' => 0,
                'created_at' => $a->created_at,
            ];
        });
        $user->setRelation('task', $tasks);

        return inertia('PMS/ViewProfile', compact('user', 'ratings', 'taskCompletionRate', 'tasks'));
    }

    public function viewPMSCurrentUserProfile()
    {
        $id = auth()->id();
        return $this->viewPMSUserProfile($id);
    }

    public function getAttendance($id)
    {
        $user = User::with([
            'attendance' => function($query) {
                $query->orderBy('created_at', 'DESC');
            },
            'leave' => function($query) {
                $query->where('status', 'approved')->orderBy('start_date', 'DESC');
            }
        ])->find($id);

        if (!$user) {
            abort(404, 'User not found');
        }

        return inertia('PMS/Attendance', compact('user'));
    }

    public function saveCertificate(Request $request){
        $request->validate([
            'title' => 'required',
            'starting_date'=> 'required',
            'completion_date' => 'required',
            'organization' =>'required',
            'file' => 'required'
        ]);

        if($request->file('file')){
            $file = $request->file('file');
            // $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "/certificate/".auth()->user()->id;
            $file->move(public_path($path),$fileName);
        }

        $cert = new Certificate();
        $cert->title = $request->title;
        $cert->starting_date = $request->starting_date;
        $cert->completion_date = $request->completion_date;
        $cert->organization = $request->organization;
        $cert->file_path = $path.'/'.$fileName;
        $cert->user_id = $request->user_id;
        $cert->save();
    }

    public function deleteCertificate(Request $request){
        $cert = Certificate::find($request->id);
        $cert->delete();
        return redirect()->back();
    }

    public function generateUserReport($id)
    {
        $user = User::with([
            'roles',
            'unit',
            'division',
            'department',
            'certificates',
            'leave' => function($q) { $q->latest('id'); },
            'attendance' => function($q) { $q->latest('created_at')->limit(30); },
        ])->findOrFail($id);

        $taskAssignments = TaskAssignment::where('assigned_to', $user->id)
            ->with(['taskSubmissions', 'assignedBy'])
            ->get();

        $totalTasks = $taskAssignments->count();
        $completedTasks = $taskAssignments->filter(fn($t) => in_array(strtolower($t->status ?? ''), ['completed', 'approved', 'closed']))->count();
        $pendingTasks = $taskAssignments->filter(fn($t) => in_array(strtolower($t->status ?? ''), ['pending', 'in progress', 'under review']))->count();
        $uncompletedTasks = $totalTasks - ($completedTasks + $pendingTasks);

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $pendingRate = $totalTasks > 0 ? round(($pendingTasks / $totalTasks) * 100) : 0;
        $uncompletedRate = $totalTasks > 0 ? round(($uncompletedTasks / $totalTasks) * 100) : 0;

        $completedTurnaroundMinutes = [];
        $onTimeCount = 0;
        foreach ($taskAssignments as $task) {
            $isComplete = in_array(strtolower($task->status ?? ''), ['completed', 'approved', 'closed']);
            if ($isComplete) {
                $sub = $task->taskSubmissions->first();
                $finishTime = $sub ? Carbon::parse($sub->created_at) : Carbon::parse($task->updated_at);
                $startTime = Carbon::parse($task->created_at);
                $completedTurnaroundMinutes[] = max(0, $finishTime->diffInMinutes($startTime));

                if ($task->due_date) {
                    $due = Carbon::parse($task->due_date);
                    if ($finishTime->lte($due)) {
                        $onTimeCount++;
                    }
                } else {
                    $onTimeCount++;
                }
            }
        }

        $avgCompletionTimeStr = 'N/A';
        if (count($completedTurnaroundMinutes) > 0) {
            $avgMins = round(array_sum($completedTurnaroundMinutes) / count($completedTurnaroundMinutes));
            $days = floor($avgMins / 1440);
            $hours = floor(($avgMins % 1440) / 60);
            $mins = $avgMins % 60;
            if ($days > 0) {
                $avgCompletionTimeStr = "{$days}d {$hours}h";
            } elseif ($hours > 0) {
                $avgCompletionTimeStr = "{$hours}h {$mins}m";
            } else {
                $avgCompletionTimeStr = "{$mins} mins";
            }
        }

        $onTimeRate = count($completedTurnaroundMinutes) > 0 
            ? round(($onTimeCount / count($completedTurnaroundMinutes)) * 100) 
            : 100;

        $taskMetrics = [
            'total_tasks' => $totalTasks,
            'completed' => $completedTasks,
            'pending' => $pendingTasks,
            'completion_rate' => $completionRate,
            'pending_rate' => $pendingRate,
            'uncompleted_rate' => $uncompletedRate,
            'avg_completion_time' => $avgCompletionTimeStr,
            'on_time_rate' => $onTimeRate,
        ];

        $kras = $user->department_id 
            ? KRA::with(['kpi' => function($q) { $q->orderBy('ordering', 'ASC'); }])
                ->where('department_id', $user->department_id)->get() 
            : collect([]);

        $attendanceLogs = Attendance::where('user_id', $user->id)->get();
        $totalDays = $attendanceLogs->count();
        $completedSessions = $attendanceLogs->filter(fn($a) => $a->login_time && $a->logout_time)->count();

        $totalMinutes = 0;
        foreach ($attendanceLogs as $log) {
            if ($log->login_time && $log->logout_time) {
                $start = Carbon::parse($log->login_time);
                $end = Carbon::parse($log->logout_time);
                $totalMinutes += max(0, $end->diffInMinutes($start));
            }
        }
        $avgDailyHours = $completedSessions > 0 ? round($totalMinutes / $completedSessions / 60, 1) . ' hrs' : '0 hrs';
        $latestSession = $attendanceLogs->sortByDesc('created_at')->first();
        $lastSessionStr = $latestSession ? Carbon::parse($latestSession->login_time ?? $latestSession->created_at)->format('d M Y, h:i A') : 'None Recorded';

        $attendanceSummary = [
            'total_days' => $totalDays,
            'completed_sessions' => $completedSessions,
            'avg_daily_hours' => $avgDailyHours,
            'last_session' => $lastSessionStr,
        ];

        $pdf = Pdf::loadView('pdfs.pms_user_scorecard', compact('user', 'taskMetrics', 'kras', 'attendanceSummary'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = "PMS_Scorecard_{$user->first_name}_{$user->last_name}.pdf";
        return $pdf->stream($fileName);
    }

    public function generateDepartmentReport($id = null)
    {
        $departmentId = $id ?: auth()->user()->department_id;
        $department = Department::find($departmentId);

        if (!$department) {
            $department = Department::first();
            $departmentId = $department ? $department->id : null;
        }

        $users = $departmentId 
            ? User::where('department_id', $departmentId)->with(['roles', 'unit', 'division'])->get() 
            : collect([]);

        $activeCount = $users->filter(fn($u) => $u->is_on_leave != 1)->count();
        $onLeaveCount = $users->filter(fn($u) => $u->is_on_leave == 1)->count();

        $kras = $departmentId 
            ? KRA::with('kpi')->where('department_id', $departmentId)->get() 
            : collect([]);

        $pdf = Pdf::loadView('pdfs.pms_department_report', compact('department', 'users', 'activeCount', 'onLeaveCount', 'kras'));
        $pdf->setPaper('a4', 'portrait');

        $deptTitle = str_replace(' ', '_', $department->title ?? ($department->name ?? 'Department'));
        return $pdf->stream("PMS_Report_{$deptTitle}.pdf");
    }

    public function generateAttendanceReport($id, Request $request)
    {
        $user = User::with(['roles', 'department', 'unit'])->findOrFail($id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Attendance::where('user_id', $user->id)->orderBy('created_at', 'DESC');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $attendanceLogs = $query->get();
        $completedCount = $attendanceLogs->filter(fn($a) => $a->login_time && $a->logout_time)->count();

        $totalMinutes = 0;
        foreach ($attendanceLogs as $log) {
            if ($log->login_time && $log->logout_time) {
                $start = Carbon::parse($log->login_time);
                $end = Carbon::parse($log->logout_time);
                $totalMinutes += max(0, $end->diffInMinutes($start));
            }
        }

        $avgDailyDuration = $completedCount > 0 
            ? round($totalMinutes / $completedCount / 60, 1) . ' hrs' 
            : '0 hrs';

        $pdf = Pdf::loadView('pdfs.pms_attendance_report', compact('user', 'attendanceLogs', 'startDate', 'endDate', 'completedCount', 'avgDailyDuration'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("Attendance_Report_{$user->first_name}_{$user->last_name}.pdf");
    }

    public function getAttendanceStatus()
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $activeAssignment = \App\Models\Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('leave_type', 'official_assignment')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $activeLeave = \App\Models\Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('leave_type', '!=', 'official_assignment')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->latest('id')
            ->first();

        $isCheckedIn = !empty($attendance?->login_time);
        $isCheckedOut = !empty($attendance?->logout_time);

        $duration = '--';
        if ($isCheckedIn && $isCheckedOut) {
            $start = Carbon::parse($attendance->login_time);
            $end = Carbon::parse($attendance->logout_time);
            $duration = $end->diffInHours($start) . 'h ' . ($end->diffInMinutes($start) % 60) . 'm';
        }

        return response()->json([
            'is_checked_in' => $isCheckedIn,
            'is_checked_out' => $isCheckedOut,
            'attendance_id' => $attendance?->id,
            'check_in_time' => $attendance?->login_time ? Carbon::parse($attendance->login_time)->format('h:i A') : null,
            'check_out_time' => $attendance?->logout_time ? Carbon::parse($attendance->logout_time)->format('h:i A') : null,
            'duration' => $duration,
            'is_official_assignment' => !empty($activeAssignment),
            'official_assignment_title' => $activeAssignment ? ($activeAssignment->destination ?: 'Official Duty') : null,
            'is_on_leave' => !empty($activeLeave),
            'leave_type' => $activeLeave?->leave_type,
        ]);
    }

    public function checkInAttendance(Request $request)
    {
        $request->validate([
            'approval_pin' => 'required|string',
        ]);

        $user = auth()->user();

        // Validate Digital Signature Password / Approval PIN
        if (empty($user->approval_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a digital signature password (approval PIN) configured in your profile. Please configure it in your profile settings.'
            ], 422);
        }

        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Digital Signature Password / PIN. Verification failed.'
            ], 422);
        }

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->latest('id')
            ->first();

        if ($attendance && $attendance->login_time && !$attendance->logout_time) {
            return response()->json([
                'success' => true,
                'message' => 'Already checked in today at ' . Carbon::parse($attendance->login_time)->format('h:i A'),
                'check_in_time' => Carbon::parse($attendance->login_time)->format('h:i A'),
                'attendance' => $attendance,
            ]);
        }

        if ($attendance && $attendance->logout_time) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'login_time' => now(),
                'date' => $today,
            ]);
        } elseif (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'login_time' => now(),
                'date' => $today,
            ]);
        } else {
            $attendance->update([
                'login_time' => now(),
                'date' => $today,
            ]);
        }

        $user->is_online = 1;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Official Attendance Check-In verified via Digital Signature at ' . Carbon::parse($attendance->login_time)->format('h:i A'),
            'attendance' => $attendance,
            'check_in_time' => Carbon::parse($attendance->login_time)->format('h:i A'),
        ]);
    }

    public function checkOutAttendance(Request $request)
    {
        $request->validate([
            'approval_pin' => 'required|string',
        ]);

        $user = auth()->user();

        // Validate Digital Signature Password / Approval PIN
        if (empty($user->approval_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a digital signature password (approval PIN) configured in your profile.'
            ], 422);
        }

        if ((string)$user->approval_pin !== (string)$request->approval_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Digital Signature Password / PIN. Verification failed.'
            ], 422);
        }

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->whereNotNull('login_time')
            ->whereNull('logout_time')
            ->latest('id')
            ->first();

        if (!$attendance) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNotNull('login_time')
                ->whereNull('logout_time')
                ->latest('id')
                ->first();
        }

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No open check-in session found for today.'
            ], 422);
        }

        $attendance->update([
            'logout_time' => now(),
        ]);

        $user->is_online = 0;
        $user->save();

        $start = Carbon::parse($attendance->login_time);
        $end = Carbon::parse($attendance->logout_time);
        $duration = $end->diffInHours($start) . 'h ' . ($end->diffInMinutes($start) % 60) . 'm';

        return response()->json([
            'success' => true,
            'message' => "Official Attendance Check-Out verified via Digital Signature. Recorded session duration: {$duration}",
            'attendance' => $attendance,
            'duration' => $duration,
            'check_out_time' => Carbon::parse($attendance->logout_time)->format('h:i A'),
        ]);
    }
}

