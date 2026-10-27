<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

     protected $user;
     protected $comment;
     protected $comment_obj;
     protected $task;

    public function __construct($comment, $user, $comment_obj = null, $task = null)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->comment_obj = $comment_obj;
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $taskId = $this->task ? $this->task->id : ($this->comment_obj && isset($this->comment_obj->taskAssignment) ? $this->comment_obj->taskAssignment->task_id : null);
        $taskTitle = $this->task ? $this->task->title : ($this->comment_obj && isset($this->comment_obj->taskAssignment) && isset($this->comment_obj->taskAssignment->task) ? $this->comment_obj->taskAssignment->task->title : 'Task Discussion');

        return [
            'user' => $this->user,
            'comment' => $this->comment,
            'comment_obj' => $this->comment_obj,
            'task_id' => $taskId,
            'task_title' => $taskTitle,
        ];
    }

     /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $taskId = $this->task ? $this->task->id : ($this->comment_obj && isset($this->comment_obj->taskAssignment) ? $this->comment_obj->taskAssignment->task_id : null);
        $taskTitle = $this->task ? $this->task->title : ($this->comment_obj && isset($this->comment_obj->taskAssignment) && isset($this->comment_obj->taskAssignment->task) ? $this->comment_obj->taskAssignment->task->title : 'Task Discussion');

        return new BroadcastMessage([
            'comment' => $this->comment,
            'user' => $this->user,
            'comment_obj' => $this->comment_obj,
            'task_id' => $taskId,
            'task_title' => $taskTitle,
        ]);
    }
}
