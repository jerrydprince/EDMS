<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    public function getRoles(){
        $user = auth()->user();
        if($user->hasRole('super admin')){
            return 'super_admin';
        }
        if($user->hasRole('super users')){
            return 'super_users';
        }
        if($user->hasRole('admin')){
            return 'admin';
        }
        if($user->hasRole('directors')){
            return 'directors';
        }
        if($user->hasRole('division heads')){
            return 'division_heads';
        }
        if($user->hasRole('unit heads')){
            return 'unit_heads';
        }else{
            return 'users';
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $role = getRoles();
        return [
            new PrivateChannel('task.'.$user->id.'.'.$role),
        ];
    }
}
