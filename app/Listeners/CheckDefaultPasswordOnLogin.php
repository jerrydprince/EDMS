<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class CheckDefaultPasswordOnLogin
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
    public function handle(object $event): void
    {
        $user = $event->user;

        // Check if the user's password is set to "password"
        if (Hash::check('password', $user->password)) {
            Auth::logout();
            redirect()->route('password.reset', Crypt::encryptString($user->email))
                      ->with('warning', 'Please reset your password for security.')
                      ->send();
        }
    }
}
