<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $recipientId;
    public $readerId;

    public function __construct($recipientId, $readerId)
    {
        $this->recipientId = $recipientId;
        $this->readerId = $readerId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'reader_id' => (int)$this->readerId,
            'recipient_id' => (int)$this->recipientId,
        ];
    }
}
