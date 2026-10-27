<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\ChainResolver;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckTaskSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks SLA deadlines on active task assignments, sends 24h reminders and escalates at 48h.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Running Task SLA Check at {$now->toDateTimeString()}...");

        $activeAssignments = TaskAssignment::with(['task', 'assignedTo', 'assignedBy'])
            ->whereIn('status', ['pending', 'under review', 'in progress', 'revision'])
            ->get();

        $remindersCount = 0;
        $escalationsCount = 0;

        foreach ($activeAssignments as $assignment) {
            if (!$assignment->task || !$assignment->assignedTo) {
                continue;
            }

            // Reference time is assignment created_at or due_at - 24 hours
            $assignedAt = $assignment->created_at ?: $now;
            $hoursElapsed = $assignedAt->diffInHours($now);

            // 1. 48h Escalation to supervisor
            if ($hoursElapsed >= 48) {
                $supervisor = ChainResolver::resolveSupervisor($assignment->assignedTo);
                if ($supervisor && $supervisor->id !== $assignment->assignedTo->id) {
                    $this->warn("Escalating Task #{$assignment->task->id} (Assigned to {$assignment->assignedTo->first_name}) to Supervisor {$supervisor->first_name}");
                    
                    try {
                        $supervisor->notify(new TaskNotification($assignment->task, $supervisor));
                        $escalationsCount++;
                    } catch (\Exception $e) {
                        Log::error("Failed to notify supervisor for task #{$assignment->task->id}: " . $e->getMessage());
                    }
                }
            }
            // 2. 24h Reminder to assignee
            elseif ($hoursElapsed >= 24) {
                $this->info("Sending 24h Reminder for Task #{$assignment->task->id} to {$assignment->assignedTo->first_name}");
                
                try {
                    $assignment->assignedTo->notify(new TaskNotification($assignment->task, $assignment->assignedTo));
                    $remindersCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send 24h reminder for task #{$assignment->task->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("SLA Check complete. Reminders sent: {$remindersCount}, Escalations: {$escalationsCount}");
        return Command::SUCCESS;
    }
}
