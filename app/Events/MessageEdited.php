<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEdited implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $channelName;

    /**
     * Create a new event instance.
     *
     * @param array $message
     * @param string $channelName Channel string, e.g. "chat.{recipient_id}" or "group.{groupId}.{groupType}"
     */
    public function __construct(array $message, string $channelName)
    {
        $this->message = $message;
        $this->channelName = $channelName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->channelName),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
