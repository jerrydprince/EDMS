<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WorkflowEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $actor;
    protected $event_type;
    protected $message;
    protected $action_url;

    /**
     * Create a new notification instance.
     *
     * @param mixed $task The task model.
     * @param mixed $actor The user who triggered the event (can be null for system events).
     * @param string $event_type The type of event (assigned, approved, returned, escalated, warning, breach, completed, reassigned, delegation, document).
     * @param string $message Custom message describing the event.
     * @param string $action_url Optional url for the user to navigate to.
     */
    public function __construct($task, $actor, $event_type, $message = '', $action_url = null)
    {
        $this->task = $task;
        $this->actor = $actor;
        $this->event_type = $event_type;
        $this->message = $message;
        $this->action_url = $action_url ?? url("/dashboard/generic-workflow/instances/{$task->id}");
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Use all standard channels as requested by the user
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isConfidential = false;
        if ($this->task && $this->task->workflowVersion && $this->task->workflowVersion->workflow) {
            $isConfidential = $this->task->workflowVersion->workflow->is_confidential;
        }

        $subject = 'Workflow Notification: ' . ucfirst($this->event_type);
        if ($isConfidential) {
            $subject = 'Confidential Notification';
            $this->message = 'You have a new confidential activity pending your review.';
        }
        
        $mail = (new MailMessage)
                    ->subject($subject)
                    ->greeting('Hello ' . $notifiable->first_name . ',')
                    ->line($this->message)
                    ->line('Task Reference: ' . $this->task->tracking_number);

        if ($this->actor) {
            $mail->line('Action by: ' . $this->actor->first_name . ' ' . $this->actor->last_name);
        }

        $mail->action('View Task', $this->action_url)
             ->line('Thank you for using the EDMS.');

        return $mail;
    }

    /**
     * Get the array representation of the notification (for Database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isConfidential = false;
        if ($this->task && $this->task->workflowVersion && $this->task->workflowVersion->workflow) {
            $isConfidential = $this->task->workflowVersion->workflow->is_confidential;
        }

        return [
            'task_id' => $this->task->id,
            'tracking_number' => $isConfidential ? 'CONFIDENTIAL' : $this->task->tracking_number,
            'actor' => $isConfidential ? null : $this->actor,
            'event_type' => $this->event_type,
            'message' => $isConfidential ? 'You have a new confidential activity pending your review.' : $this->message,
            'action_url' => $this->action_url,
            'type' => 'workflow_event' // Useful for JS grouping
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $isConfidential = false;
        if ($this->task && $this->task->workflowVersion && $this->task->workflowVersion->workflow) {
            $isConfidential = $this->task->workflowVersion->workflow->is_confidential;
        }

        return new BroadcastMessage([
            'task_id' => $this->task->id,
            'tracking_number' => $isConfidential ? 'CONFIDENTIAL' : $this->task->tracking_number,
            'actor' => $isConfidential ? null : $this->actor,
            'event_type' => $this->event_type,
            'message' => $isConfidential ? 'You have a new confidential activity pending your review.' : $this->message,
            'action_url' => $this->action_url,
            'type' => 'workflow_event'
        ]);
    }
}
