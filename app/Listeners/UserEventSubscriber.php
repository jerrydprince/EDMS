<?php

namespace App\Listeners;

namespace App\Listeners;
 
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
 
class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void {
        $user = $event->user;
        $user->is_online = 1;
        $user->save();
    }
 
    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void {
        $user = $event->user;
       if($user){
        $user->is_online = 0;
        $user->save();
       }
    }
 
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Logout::class => 'handleUserLogout',
        ];
    }
}
