<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncLeaveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pms:sync-leave-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes users is_on_leave and is_on_official_assignment status based on approved leave and duty schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Synchronizing leave and official duty status for date: {$today}");

        // 1. Fetch all approved leaves covering today
        $activeLeaves = Leave::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        $activeUserIds = [];

        foreach ($activeLeaves as $leave) {
            $user = User::find($leave->user_id);
            if (!$user) continue;

            $activeUserIds[] = $user->id;

            if ($leave->leave_type === 'official_assignment') {
                $user->is_on_official_assignment = true;
                $user->is_on_leave = false;
            } else {
                $user->is_on_leave = true;
                $user->is_on_official_assignment = false;
            }

            if ($user->isDirty()) {
                $user->save();
                $this->line("Activated [{$leave->leave_type}] for user #{$user->id} ({$user->name})");
            }
        }

        // 2. Reset users who are currently marked as on leave / duty but have no active approved leave today
        $usersToReset = User::where(function($query) {
                $query->where('is_on_leave', true)
                      ->orWhere('is_on_official_assignment', true);
            })
            ->whereNotIn('id', $activeUserIds)
            ->get();

        foreach ($usersToReset as $user) {
            $user->is_on_leave = false;
            $user->is_on_official_assignment = false;
            $user->save();
            $this->line("Deactivated leave/assignment for user #{$user->id} ({$user->name})");
        }

        $summary = sprintf(
            "Sync complete. %d staff currently on leave/duty. %d staff returned to active duty.",
            count($activeUserIds),
            $usersToReset->count()
        );

        $this->info($summary);
        Log::info("pms:sync-leave-status: " . $summary);

        return Command::SUCCESS;
    }
}
