<?php

namespace App\Listeners;

use App\Events\WorkflowCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessPoolPostingCompletion
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WorkflowCompleted $event): void
    {
        $task = $event->task;

        if ($task && $task->workflowVersion && $task->workflowVersion->workflow) {
            $workflowTitle = $task->workflowVersion->workflow->title;
            $formData = $task->form_data;

            if (is_array($formData)) {
                if ($workflowTitle === 'ADM-007 Pool Posting' || $workflowTitle === 'ADM-008 Redeployment') {
                    if (isset($formData['target_user_id']) && isset($formData['mda_name'])) {
                        \App\Models\Posting::create([
                            'user_id' => $formData['target_user_id'],
                            'mda_name' => $formData['mda_name'],
                            'workflow_task_id' => $task->id,
                            'assumption_date' => $formData['assumption_date'] ?? null
                        ]);
                    }
                } elseif ($workflowTitle === 'ADM-008 Recall') {
                    if (isset($formData['target_user_id'])) {
                        \App\Models\Posting::create([
                            'user_id' => $formData['target_user_id'],
                            'mda_name' => 'OAGF Headquarters Pool',
                            'workflow_task_id' => $task->id,
                            'assumption_date' => now()->format('Y-m-d')
                        ]);
                    }
                } elseif ($workflowTitle === 'ADM-008 Replacement') {
                    if (isset($formData['target_user_id']) && isset($formData['replaced_user_id']) && isset($formData['mda_name'])) {
                        // Incoming staff posted
                        \App\Models\Posting::create([
                            'user_id' => $formData['target_user_id'],
                            'mda_name' => $formData['mda_name'],
                            'workflow_task_id' => $task->id,
                            'assumption_date' => $formData['assumption_date'] ?? null
                        ]);

                        // Replaced staff recalled
                        \App\Models\Posting::create([
                            'user_id' => $formData['replaced_user_id'],
                            'mda_name' => 'OAGF Headquarters Pool',
                            'workflow_task_id' => $task->id,
                            'assumption_date' => now()->format('Y-m-d')
                        ]);
                    }
                }
            }
        }
    }
}
