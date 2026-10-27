<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public $message;
    public $sender;
    public $options;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $sender, array $options = [])
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->options = $options;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification (for Database).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $senderName = $this->sender ? trim("{$this->sender->first_name} {$this->sender->last_name}") : 'Colleague';
        $isGroup = !empty($this->options['is_group']);
        $groupName = $this->options['group_name'] ?? null;
        $msgText = $this->message->message ?: 'Sent an attachment';

        return [
            'is_chat' => true,
            'is_group' => $isGroup,
            'group_type' => $this->options['group_type'] ?? null,
            'group_id' => $this->options['group_id'] ?? null,
            'group_name' => $groupName,
            'chat_message_id' => $this->message->id,
            'sender_id' => $this->sender?->id,
            'sender' => [
                'id' => $this->sender?->id,
                'first_name' => $this->sender?->first_name,
                'last_name' => $this->sender?->last_name,
                'profile_image' => $this->sender?->profile_image,
                'email' => $this->sender?->email,
            ],
            'message' => $msgText,
            'action_url' => $isGroup 
                ? '/dashboard/chat?group_id=' . ($this->options['group_id'] ?? '') . '&group_type=' . ($this->options['group_type'] ?? '')
                : '/dashboard/chat?user_id=' . ($this->sender?->id ?? ''),
            'created_at' => $this->message->created_at ? $this->message->created_at->toIso8601String() : now()->toIso8601String(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
