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
                'uptime_percentage' => '0.00',
                'downtime_hours' => 0,
                'downtime_minutes' => 0,
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
        $isOnline = $latestStatus ? $latestStatus->is_online : false;
        return response()->json([
            'uptime_percentage' => $formattedUptime,
            'downtime' => $formattedDowntime,
            'is_online' => $isOnline ? 'Online' : 'Offline',
        ]);
    }
    public function getUserLogins()
    {
        $logins = LoginActivity::all();
        return response()->json($logins);
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
}
