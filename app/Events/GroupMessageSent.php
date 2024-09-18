<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent  implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $message;
    private $groupId;
    private $groupType;
    /**
     * Create a new event instance.
     */
    public function __construct(array $message, $groupId, $groupType)
    {
        $this->message = $message;
        $this->groupId = $groupId;
        $this->groupType = $groupType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('group.' . $this->groupId.'.'.$this->groupType);
    }
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
        ];
    }

}
