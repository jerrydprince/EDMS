<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Models\Task;
use App\Models\Mail;
use App\Models\FileMovement;
use App\Models\Posting;
use App\Models\Leave;
use App\Models\User;
use App\Models\Department;

class ReportsController extends Controller
{
    /**
     * Render the Reports and Analytics Dashboard
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Fetch aggregated analytics data for the active user
     */
    public function getAnalytics(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->input('dateFilter', '30'); // default 30 days
        $startDate = now()->subDays((int)$dateFilter)->startOfDay();
        $endDate = now()->endOfDay();

        // 1. Scoping Logic
        $taskQuery = Task::whereBetween('created_at', [$startDate, $endDate]);
        $mailQuery = Mail::whereBetween('created_at', [$startDate, $endDate]);
        $movementQuery = FileMovement::whereBetween('created_at', [$startDate, $endDate]);
        $postingQuery = Posting::whereBetween('created_at', [$startDate, $endDate]);
        $leaveQuery = Leave::whereBetween('created_at', [$startDate, $endDate]);

        if ($user->hasRole('super admin') || $user->hasRole('super user')) {
            // Global scope - No additional restrictions
        } elseif ($user->department_id && $user->hasRole('director')) {
            $taskQuery->where('assigned_by_department', $user->department_id);
            $mailQuery->where('department_id', $user->department_id);
            // FileMovement doesn't have department_id natively, filter by sender/receiver unit/dept if possible
            // We'll keep it simple: filter by sent_by within department
            $userIds = User::where('department_id', $user->department_id)->pluck('id');
            $movementQuery->whereIn('sent_by', $userIds);
            $postingQuery->where('department_id', $user->department_id);
            $leaveQuery->whereIn('user_id', $userIds);
        } elseif ($user->division_id && $user->hasRole('division head')) {
            $taskQuery->where('assigned_by_division', $user->division_id);
            $mailQuery->where('division_id', $user->division_id);
            $userIds = User::where('division_id', $user->division_id)->pluck('id');
            $movementQuery->whereIn('sent_by', $userIds);
            $leaveQuery->whereIn('user_id', $userIds);
        } elseif ($user->unit_id && $user->hasRole('unit head')) {
            $taskQuery->where('assigned_by_unit', $user->unit_id);
            $mailQuery->where('unit_id', $user->unit_id);
            $userIds = User::where('unit_id', $user->unit_id)->pluck('id');
            $movementQuery->whereIn('sent_by', $userIds);
            $leaveQuery->whereIn('user_id', $userIds);
        } else {
            // Action officer - only assigned tasks/mails
            $taskQuery->whereHas('assignedToMe', function($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
            if (!$user->hasRole('registry')) {
                $mailQuery->where('user_id', $user->id);
            }
            $movementQuery->where('sent_by', $user->id);
            $leaveQuery->where('user_id', $user->id);
        }

        // 2. Data Aggregations

        // Workflow Status Distribution
        $workflowStatus = (clone $taskQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Registry & Mail Trends (Line chart data over time)
        // Group by Date
        $registryTrendsRaw = (clone $mailQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $registryTrends = [
            'labels' => $registryTrendsRaw->pluck('date'),
            'data' => $registryTrendsRaw->pluck('count'),
        ];

        // File Movement Tracking
        $fileTracking = (clone $movementQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $fileTrackingTrends = [
            'labels' => $fileTracking->pluck('date'),
            'data' => $fileTracking->pluck('count'),
        ];

        // Departmental Performance (Only relevant for Global or Dept scope)
        $departmentPerformance = [];
        if ($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasRole('super user')) {
            $deptData = Task::select('assigned_by_department', DB::raw('count(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->whereNotNull('assigned_by_department')
                ->groupBy('assigned_by_department')
                ->get();
                
            $labels = [];
            $data = [];
            foreach($deptData as $d) {
                $dept = Department::find($d->assigned_by_department);
                if ($dept) {
                    $labels[] = $dept->title;
                    $data[] = $d->count;
                }
            }
            $departmentPerformance = [
                'labels' => $labels,
                'data' => $data
            ];
        }

        // SLA Compliance
        $now = now()->format('Y-m-d');
        // Total Tasks with Due Dates
        $tasksWithDueDate = clone $taskQuery;
        
        $slaBreaches = (clone $tasksWithDueDate)
            ->where('due_date', '<', $now)
            ->where('status', '!=', 'completed')
            ->count();
            
        $slaCompliant = (clone $tasksWithDueDate)
            ->where(function($q) use ($now) {
                $q->where('status', 'completed')
                  ->orWhere('due_date', '>=', $now);
            })->count();

        $slaData = [
            'labels' => ['Compliant', 'Breached'],
            'data' => [$slaCompliant, $slaBreaches]
        ];

        // Pool & Welfare Metrics
        $poolCount = (clone $postingQuery)->count();
        $leaveCount = (clone $leaveQuery)->count();

        // Compile Response
        return response()->json([
            'workflowStatus' => $workflowStatus,
            'registryTrends' => $registryTrends,
            'fileTrackingTrends' => $fileTrackingTrends,
            'departmentPerformance' => $departmentPerformance,
            'slaData' => $slaData,
            'welfare' => [
                'pool_deployments' => $poolCount,
                'leave_requests' => $leaveCount
            ]
        ]);
    }
}
