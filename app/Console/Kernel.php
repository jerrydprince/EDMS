<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('emails:fetch --unseen')
             ->everyThreeMinutes()
             ->withoutOverlapping()
             ->runInBackground();
             
        $schedule->command('workflow:monitor-slas')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->runInBackground();
             
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');
        $schedule->command('pms:sync-leave-status')->daily()->at('00:01')->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
