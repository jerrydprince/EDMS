<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Models\Mail;
use Illuminate\Support\Facades\Auth;

class DashboardMetricsController extends Controller
{
    public function getMetrics()
    {
        $user = Auth::user();
        
        // Scope logic
        $taskQuery = Task::query();
        $mailQuery = Mail::query();
        
        if ($user->hasRole('super admin') || $user->hasRole('admin')) {
            // Global scope - AGF / Super Admin
        } elseif ($user->department_id && $user->hasRole('department head')) {
            $taskQuery->where('assigned_by_department', $user->department_id);
            $mailQuery->where('department_id', $user->department_id);
        } elseif ($user->division_id && $user->hasRole('division head')) {
            $taskQuery->where('assigned_by_division', $user->division_id);
            $mailQuery->where('division_id', $user->division_id);
        } elseif ($user->unit_id && $user->hasRole('unit head')) {
            $taskQuery->where('assigned_by_unit', $user->unit_id);
            $mailQuery->where('unit_id', $user->unit_id);
        } else {
            // Action officer - only assigned tasks
            $taskQuery->whereHas('assignedToMe', function($q) use ($user) {
                // assignedToMe handles filtering by auth()->user()->id internally in some relations, 
                // but let's just explicitly filter assignments:
                $q->where('assigned_to', $user->id);
            });
            // Regular users shouldn't see global mails unless they are registry.
            // Let's assume registry has a role 'registry'.
            if (!$user->hasRole('registry')) {
                $mailQuery->where('user_id', $user->id);
            }
        }

        $now = now()->format('Y-m-d');

        $metrics = [
            'pending' => (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed' => (clone $taskQuery)->where('status', 'completed')->count(),
            'returned' => (clone $taskQuery)->whereIn('status', ['returned', 'rejected'])->count(),
            'due' => (clone $taskQuery)->where('due_date', $now)->where('status', '!=', 'completed')->count(),
            'overdue' => (clone $taskQuery)->where('due_date', '<', $now)->where('status', '!=', 'completed')->count(),
            'workload' => (clone $taskQuery)->where('status', '!=', 'completed')->count(),
            'received' => 0, // Not tracked in Mail model currently
            'dispatched' => (clone $mailQuery)->count(), // Total mails in scope
            'escalations' => 0, // Not explicitly tracked
            'sla_breaches' => (clone $taskQuery)->where('due_date', '<', $now)->where('status', '!=', 'completed')->count(),
        ];

        return response()->json($metrics);
    }
}
