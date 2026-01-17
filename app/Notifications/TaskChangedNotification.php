<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $task_id;
    protected $user;
    protected $status;

    public function __construct($task_id, $user, $status)
    {
        $this->task_id = $task_id;
        $this->user = $user;
        $this->status = $status;
    }

    /***
     *  $status = [
            'created' => 'created',
            'assigned' => 'assigned',
            'submitted' => 'submitted',
            'review' => 'under review',
            'completed' => 'completed',
        ];
     */
    public function getStatusMessage(){
        $status = $this->status;
        if($status == 'assigned') {
            return $this->user->last_name.' '.$this->user->first_name.' assigned a task to you';
        } elseif($status == 'submitted') {
            return $this->user->last_name.' '.$this->user->first_name.' submitted a task';
        } elseif($status == 'rejected') {
            return $this->user->last_name.' '.$this->user->first_name.' requested for task revision';
        } elseif($status == 'completed') {
            return $this->user->last_name.' '.$this->user->first_name.' marked your task as completed';
        } else {
            return 'updated';
        }
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
        return [
            'task_id' => $this->task_id,
            'user' => $this->user,
            'message' => $this->getStatusMessage(),
            'status' => $this->status,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'task_id' => $this->task_id,
            'user' => $this->user,
            'message' => $this->getStatusMessage(),
            'status' => $this->status,
        ]);
    }
}
