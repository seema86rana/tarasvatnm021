<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Register the Artisan commands for the application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\MachineStatusReport::class,
        \App\Console\Commands\MachineStopReport::class,
        \App\Console\Commands\MachineStopAlert::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run daily at midnight (00:00)
        $schedule->command('machine:status-report')->daily();

        // Run daily at midnight (00:00)
        $schedule->command('machine:stop-report')->daily();

        // Run every 30 minutes
        $schedule->command('machine:stop-alert')->everyThirtyMinutes();

        Log::info("\n\n Call Kernal \n\n");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
