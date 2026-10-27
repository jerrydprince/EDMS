<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorWorkflowSLAs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:monitor-slas';
    protected $description = 'Monitor generic workflow SLAs for warnings and breaches';

    public function handle()
    {
        $this->info('Starting SLA Monitoring...');

        $activeSteps = \App\Models\TaskActiveStep::where('status', 'in_progress')
            ->where('is_paused', false)
            ->whereNotNull('due_date')
            ->get();

        foreach ($activeSteps as $step) {
            $sla = \App\Models\WorkflowSla::where('workflow_step_id', $step->step_id)->first();
            if (!$sla) continue;

            $now = now();
            $dueDate = \Carbon\Carbon::parse($step->due_date);

            $task = \App\Models\Task::find($step->task_id);
            $systemUserId = $task && $task->created_by ? $task->created_by : 1;

            // 1. Check for Breach
            if ($now->greaterThan($dueDate) && !$step->is_breached) {
                $step->is_breached = true;
                $step->breached_at = $now;
                $step->save();

                \App\Models\WorkflowLog::create([
                    'task_id' => $step->task_id,
                    'action_by' => $systemUserId, // System
                    'action_type' => 'sla_breach',
                    'comments' => 'SLA breached.'
                ]);

                // Escalation Action
                if ($sla->escalation_role) {
                    $escalationUsers = \App\Models\User::role($sla->escalation_role)->pluck('id');
                    
                    $assignedBy = $systemUserId;
                    foreach ($escalationUsers as $userId) {
                        \App\Models\TaskAssignment::updateOrCreate(
                            ['task_id' => $step->task_id, 'assigned_to' => $userId],
                            ['status' => 'pending', 'assigned_by' => $assignedBy]
                        );
                    }

                    if ($sla->remove_original_assignee) {
                        \App\Models\TaskAssignment::where('task_id', $step->task_id)
                            ->whereNotIn('assigned_to', $escalationUsers)
                            ->update(['status' => 'uncompleted']);
                    }
                    
                    // Notify Escalation users
                    foreach ($escalationUsers as $userId) {
                        $eUser = \App\Models\User::find($userId);
                        if ($eUser) {
                            $eUser->notify(new \App\Notifications\WorkflowEventNotification(
                                $task,
                                null,
                                'escalation',
                                'A task has been escalated to your role due to an SLA breach.'
                            ));
                        }
                    }
                }
                
                // Notify current assignees of breach
                $currentAssignees = \App\Models\TaskAssignment::where('task_id', $step->task_id)
                    ->where('status', 'assigned')
                    ->pluck('assigned_to');
                
                foreach ($currentAssignees as $userId) {
                    $cUser = \App\Models\User::find($userId);
                    if ($cUser) {
                        $cUser->notify(new \App\Notifications\WorkflowEventNotification(
                            $task,
                            null,
                            'breach',
                            'The SLA for your assigned task has breached.'
                        ));
                    }
                }
            }
            // 2. Check for Warning
            elseif ($sla->warning_hours > 0 && !$step->warning_sent_at) {
                $warningTime = $dueDate->copy()->subHours($sla->warning_hours);
                if ($now->greaterThanOrEqualTo($warningTime)) {
                    $step->warning_sent_at = $now;
                    $step->save();

                    \App\Models\WorkflowLog::create([
                        'task_id' => $step->task_id,
                        'action_by' => $systemUserId, // System
                        'action_type' => 'sla_warning',
                        'comments' => 'SLA warning triggered.'
                    ]);
                    
                    // Dispatch notification (to be implemented via system Notification)
                    $currentAssignees = \App\Models\TaskAssignment::where('task_id', $step->task_id)
                        ->where('status', 'assigned')
                        ->pluck('assigned_to');
                    
                    foreach ($currentAssignees as $userId) {
                        $cUser = \App\Models\User::find($userId);
                        if ($cUser) {
                            $cUser->notify(new \App\Notifications\WorkflowEventNotification(
                                $task,
                                null,
                                'warning',
                                'Warning: Your task SLA is approaching its due date.'
                            ));
                        }
                    }
                }
            }
        }

        $this->info('SLA Monitoring Complete.');
    }
}
