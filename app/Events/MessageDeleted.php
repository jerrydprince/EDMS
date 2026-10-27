<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $isGroup;
    public $channelId; // Either group_id or recipient_id

    public function __construct($messageId, $isGroup, $channelId)
    {
        $this->messageId = $messageId;
        $this->isGroup = $isGroup;
        $this->channelId = $channelId;
    }

    public function broadcastOn()
    {
        if ($this->isGroup) {
            return new PrivateChannel('groupchat.' . $this->channelId);
        } else {
            return new PrivateChannel('chat.' . $this->channelId);
        }
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'is_group' => $this->isGroup
        ];
    }
}
