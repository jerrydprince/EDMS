<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Events\Dispatcher;

class StoreBrowserInSession
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $sessionId = session()->getId();
        $agent = new Agent();
        $browser = $agent->browser();

        DB::table('sessions')->where('id', $sessionId)->update(['browser'=>$browser]);
    }

    public function handleUserLogin(Login $event): void {
        $user = $event->user;
        $sessionId = session()->getId();
        $agent = new Agent();
        $browser = $agent->browser();

        DB::table('sessions')->where('id', $sessionId)->update(['browser'=>$browser]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            Login::class,
            [StoreBrowserInSession::class, 'handleUserLogin']
        );
    }

}
