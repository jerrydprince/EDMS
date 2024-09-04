<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent Implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private $message;
    private $recipientId;
    public function __construct(array $message, $recipientId)
    {
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    //reverb

    // public function broadcastOn()
    // {
    //     return new Channel('chat.' . $this->recipientId);
    // }
    public function broadcastOn()
    {
        // Send the message to a private channel
        return new PrivateChannel('chat.'. $this->recipientId);
    }
    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
    // public function broadcastWith()
    // {
    //     return ['message' => $this->message->load('user')];
    // }
}

