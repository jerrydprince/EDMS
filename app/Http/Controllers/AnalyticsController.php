<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\ServerStatus;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function analytics()
    {
        return Inertia::render('Analytics/Analytics');
    }
    public function getServerStatus()
    {
        // Define the time range to calculate uptime/downtime (last 24 hours)
        $startTime = Carbon::now()->subDay(); // 24 hours ago
        $endTime = Carbon::now();

        // Get all status entries in this time range
        $statuses = ServerStatus::whereBetween('checked_at', [$startTime, $endTime])
            ->orderBy('checked_at')
            ->get();

        if ($statuses->isEmpty()) {
            // No data available
            return response()->json([
                'uptime_percentage' => '100',
                'downtime' => 'No downtime',
                'is_online' => 'Online',
            ]);
        }

        // Initialize variables for calculating uptime and downtime
        $totalUptime = 0; // Total uptime in minutes
        $totalDowntime = 0; // Total downtime in minutes

        // Convert checked_at fields to Carbon instances and iterate through statuses
        $previousStatus = $statuses->first();
        foreach ($statuses->skip(1) as $status) {
            // Convert checked_at to Carbon instance
            $previousCheckedAt = Carbon::parse($previousStatus->checked_at);
            $currentCheckedAt = Carbon::parse($status->checked_at);

            $duration = $currentCheckedAt->diffInMinutes($previousCheckedAt);

            if ($previousStatus->is_online) {
                $totalUptime += $duration;
            } else {
                $totalDowntime += $duration;
            }

            $previousStatus = $status;
        }

        // Convert last status time to Carbon instance
        $previousCheckedAt = Carbon::parse($previousStatus->checked_at);
        $duration = $endTime->diffInMinutes($previousCheckedAt);

        if ($previousStatus->is_online) {
            $totalUptime += $duration;
        } else {
            $totalDowntime += $duration;
        }

        // Calculate uptime percentage
        $totalMinutes = $totalUptime + $totalDowntime;
        $uptimePercentage = ($totalMinutes > 0) ? ($totalUptime / $totalMinutes) * 100 : 0;

        // Convert downtime to hours and minutes
        $downtimeHours = intdiv($totalDowntime, 60);
        $downtimeMinutes = $totalDowntime % 60;

        $downtimeHours = floor($downtimeMinutes / 60); // Convert minutes to hours
        $downtimeRemainingMinutes = $downtimeMinutes % 60; // Get the remaining minutes

        $formattedDowntime = '';

        if ($downtimeHours > 0) {
            $formattedDowntime .= $downtimeHours . ' hr';
        }

        if ($downtimeRemainingMinutes > 0) {
            if ($downtimeHours > 0) {
                $formattedDowntime .= ' '; // Add space between hours and minutes
            }
            $formattedDowntime .= $downtimeRemainingMinutes . ' mins';
        }

        // Handle cases where there is only 1 hour or 1 minute for proper grammar
        if ($downtimeHours == 1) {
            $formattedDowntime = str_replace('1 hr', '1 hour', $formattedDowntime);
        }

        if ($downtimeRemainingMinutes == 1) {
            $formattedDowntime = str_replace('1 mins', '1 min', $formattedDowntime);
        }
        if (empty($formattedDowntime)) {
            $formattedDowntime = 'No downtime';
        }

        if (intval($uptimePercentage) == $uptimePercentage) {
            $formattedUptime = intval($uptimePercentage); // Remove decimals
        } else {
            $formattedUptime = number_format($uptimePercentage, 2); // Keep 2 decimal places
        }


        $latestStatus = ServerStatus::orderBy('checked_at', 'desc')->first();
        $isOnline = $latestStatus ? $latestStatus->is_online : true;
        return response()->json([
            'uptime_percentage' => $formattedUptime,
            'downtime' => $formattedDowntime,
            'is_online' => $isOnline ? 'Online' : 'Offline',
        ]);
    }
    public function getUserLogins()
    {
        $logins = LoginActivity::orderBy('created_at', 'desc')->take(50)->get();
        return response()->json($logins);
    }

    public function getAGFSummary()
    {
        $totalDocuments = \App\Models\TaskDoc::count() + \App\Models\ArchiveDocs::count();
        $activeUsers = \App\Models\User::where('is_approved', 1)->count();
        $workflowsCompleted = \App\Models\Task::where('status', 'Completed')->count();
        $totalDepartments = \App\Models\Department::count();
        $totalDivisions = \App\Models\Division::count();
        $totalUnits = \App\Models\Unit::count();

        $departmentalStatus = \App\Models\Department::withCount([
            'task as pending_tasks' => function ($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            },
            'task as completed_tasks' => function ($query) {
                $query->where('status', 'Completed')->orWhere('status', 'completed');
            }
        ])->get()->map(function($dept) {
            return [
                'department' => $dept->title,
                'pending' => $dept->pending_tasks,
                'completed' => $dept->completed_tasks
            ];
        });

        $highPriorityTasks = \App\Models\Task::where('priority', 'High')
            ->where(function($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            })
            ->with(['assignedBy', 'workflowVersion'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                [
                    'title' => "Total documents count",
                    'count' => number_format($totalDocuments),
                ],
                [
                    'title' => "Active users",
                    'count' => number_format($activeUsers),
                ],
                [
                    'title' => "Workflow completed",
                    'count' => number_format($workflowsCompleted),
                ],
                [
                    'title' => "Total Departments",
                    'count' => number_format($totalDepartments),
                ],
                [
                    'title' => "Total Divisions",
                    'count' => number_format($totalDivisions),
                ],
                [
                    'title' => "Total sections",
                    'count' => number_format($totalUnits),
                ]
            ],
            'chart_data' => $departmentalStatus,
            'high_priority_tasks' => $highPriorityTasks
        ]);
    }
    
    // {
    //     // Define the time range to calculate uptime/downtime (last 24 hours)
    //     $startTime = Carbon::now()->subDay(); // 24 hours ago
    //     $endTime = Carbon::now();

    //     // Total number of checks in this time range
    //     $totalChecks = ServerStatus::whereBetween('checked_at', [$startTime, $endTime])->count();

    //     // Number of successful online checks
    //     $onlineChecks = ServerStatus::where('is_online', true)
    //                         ->whereBetween('checked_at', [$startTime, $endTime])
    //                         ->count();

    //     // Calculate uptime percentage
    //     $uptimePercentage = ($totalChecks > 0) ? ($onlineChecks / $totalChecks) * 100 : 0;

    //     // Calculate downtime (total offline checks = downtime in minutes)
    //     $offlineChecks = $totalChecks - $onlineChecks;
    //     $downtimeInMinutes = $offlineChecks;

    //     // Convert downtime to hours and minutes
    //     $downtimeHours = intdiv($downtimeInMinutes, 60);
    //     $downtimeMinutes = $downtimeInMinutes % 60;

    //     return response()->json([
    //         'uptime_percentage' => number_format($uptimePercentage, 2),
    //         'downtime_hours' => $downtimeHours,
    //         'downtime_minutes' => $downtimeMinutes,
    //     ]);
    // }

    public function getDirectorSummary()
    {
        $user = auth()->user();
        if (!$user->department_id) {
            return response()->json([
                'summary' => [],
                'chart_data' => [],
                'high_priority_tasks' => []
            ]);
        }

        $deptId = $user->department_id;

        $totalDocuments = \App\Models\TaskDoc::whereHas('task', function($query) use ($deptId) {
            $query->where('assigned_by_department', $deptId)
                  ->orWhereHas('departmentTask', function($q) use ($deptId) {
                      $q->where('departments.id', $deptId);
                  });
        })->count();

        $activeUsers = \App\Models\User::where('department_id', $deptId)->where('is_approved', 1)->count();
        $workflowsCompleted = \App\Models\Task::where('status', 'Completed')
            ->where(function($query) use ($deptId) {
                $query->where('assigned_by_department', $deptId)
                      ->orWhereHas('departmentTask', function($q) use ($deptId) {
                          $q->where('departments.id', $deptId);
                      });
            })->count();

        $totalDivisions = \App\Models\Division::where('department_id', $deptId)->count();
        $totalUnits = \App\Models\Unit::whereHas('division', function($query) use ($deptId) {
            $query->where('department_id', $deptId);
        })->count();

        $departmentalStatus = \App\Models\Department::where('id', $deptId)->withCount([
            'task as pending_tasks' => function ($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            },
            'task as completed_tasks' => function ($query) {
                $query->where('status', 'Completed')->orWhere('status', 'completed');
            }
        ])->get()->map(function($dept) {
            return [
                'department' => $dept->title,
                'pending' => $dept->pending_tasks,
                'completed' => $dept->completed_tasks
            ];
        });

        $highPriorityTasks = \App\Models\Task::where('priority', 'High')
            ->where(function($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            })
            ->where(function($query) use ($deptId) {
                $query->where('assigned_by_department', $deptId)
                      ->orWhereHas('departmentTask', function($q) use ($deptId) {
                          $q->where('departments.id', $deptId);
                      });
            })
            ->with(['assignedBy', 'workflowVersion'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                [
                    'title' => "Department Documents",
                    'count' => number_format($totalDocuments),
                ],
                [
                    'title' => "Department Staff",
                    'count' => number_format($activeUsers),
                ],
                [
                    'title' => "Workflows Completed",
                    'count' => number_format($workflowsCompleted),
                ],
                [
                    'title' => "Total Divisions",
                    'count' => number_format($totalDivisions),
                ],
                [
                    'title' => "Total Units",
                    'count' => number_format($totalUnits),
                ]
            ],
            'chart_data' => $departmentalStatus,
            'high_priority_tasks' => $highPriorityTasks
        ]);
    }

    public function getDivisionSummary()
    {
        $user = auth()->user();
        if (!$user->division_id) {
            return response()->json([
                'summary' => [],
                'chart_data' => [],
                'high_priority_tasks' => []
            ]);
        }

        $divId = $user->division_id;

        $totalDocuments = \App\Models\TaskDoc::whereHas('task', function($query) use ($divId) {
            $query->where('assigned_by_division', $divId)
                  ->orWhereHas('divisionTask', function($q) use ($divId) {
                      $q->where('divisions.id', $divId);
                  });
        })->count();

        $activeUsers = \App\Models\User::where('division_id', $divId)->where('is_approved', 1)->count();
        $workflowsCompleted = \App\Models\Task::where('status', 'Completed')
            ->where(function($query) use ($divId) {
                $query->where('assigned_by_division', $divId)
                      ->orWhereHas('divisionTask', function($q) use ($divId) {
                          $q->where('divisions.id', $divId);
                      });
            })->count();

        $totalUnits = \App\Models\Unit::where('division_id', $divId)->count();

        // Division level doesn't need to break down by division, just show its own status
        $divisionStatus = \App\Models\Division::where('id', $divId)->withCount([
            'task as pending_tasks' => function ($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            },
            'task as completed_tasks' => function ($query) {
                $query->where('status', 'Completed')->orWhere('status', 'completed');
            }
        ])->get()->map(function($div) {
            return [
                'department' => $div->title, // Keep key 'department' for the UI component
                'pending' => $div->pending_tasks,
                'completed' => $div->completed_tasks
            ];
        });

        $highPriorityTasks = \App\Models\Task::where('priority', 'High')
            ->where(function($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            })
            ->where(function($query) use ($divId) {
                $query->where('assigned_by_division', $divId)
                      ->orWhereHas('divisionTask', function($q) use ($divId) {
                          $q->where('divisions.id', $divId);
                      });
            })
            ->with(['assignedBy', 'workflowVersion'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                [
                    'title' => "Division Documents",
                    'count' => number_format($totalDocuments),
                ],
                [
                    'title' => "Division Staff",
                    'count' => number_format($activeUsers),
                ],
                [
                    'title' => "Workflows Completed",
                    'count' => number_format($workflowsCompleted),
                ],
                [
                    'title' => "Total Units",
                    'count' => number_format($totalUnits),
                ]
            ],
            'chart_data' => $divisionStatus,
            'high_priority_tasks' => $highPriorityTasks
        ]);
    }

    public function getUnitSummary()
    {
        $user = auth()->user();
        if (!$user->unit_id) {
            return response()->json([
                'summary' => [],
                'chart_data' => [],
                'high_priority_tasks' => []
            ]);
        }

        $unitId = $user->unit_id;

        $totalDocuments = \App\Models\TaskDoc::whereHas('task', function($query) use ($unitId) {
            $query->where('assigned_by_unit', $unitId)
                  ->orWhereHas('unitTask', function($q) use ($unitId) {
                      $q->where('units.id', $unitId);
                  });
        })->count();

        $activeUsers = \App\Models\User::where('unit_id', $unitId)->where('is_approved', 1)->count();
        $workflowsCompleted = \App\Models\Task::where('status', 'Completed')
            ->where(function($query) use ($unitId) {
                $query->where('assigned_by_unit', $unitId)
                      ->orWhereHas('unitTask', function($q) use ($unitId) {
                          $q->where('units.id', $unitId);
                      });
            })->count();

        // Unit level shows its own status
        $unitStatus = \App\Models\Unit::where('id', $unitId)->withCount([
            'task as pending_tasks' => function ($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            },
            'task as completed_tasks' => function ($query) {
                $query->where('status', 'Completed')->orWhere('status', 'completed');
            }
        ])->get()->map(function($unit) {
            return [
                'department' => $unit->title, // Keep key 'department' for the UI component
                'pending' => $unit->pending_tasks,
                'completed' => $unit->completed_tasks
            ];
        });

        $highPriorityTasks = \App\Models\Task::where('priority', 'High')
            ->where(function($query) {
                $query->where('status', 'Pending')->orWhere('status', 'pending');
            })
            ->where(function($query) use ($unitId) {
                $query->where('assigned_by_unit', $unitId)
                      ->orWhereHas('unitTask', function($q) use ($unitId) {
                          $q->where('units.id', $unitId);
                      });
            })
            ->with(['assignedBy', 'workflowVersion'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                [
                    'title' => "Unit Documents",
                    'count' => number_format($totalDocuments),
                ],
                [
                    'title' => "Unit Staff",
                    'count' => number_format($activeUsers),
                ],
                [
                    'title' => "Workflows Completed",
                    'count' => number_format($workflowsCompleted),
                ]
            ],
            'chart_data' => $unitStatus,
            'high_priority_tasks' => $highPriorityTasks
        ]);
    }
}
