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
        $schedule->command('tickets:report-open')
            ->hourly()
            ->between('9:00', '18:00')
            ->days([1, 2, 3, 4, 5])
            ->before(function () {
                \Log::info('tickets:report-open is about to run');
            });

        $schedule->command('tickets:notify-urgent')
            ->everyFiveMinutes()
            ->between('9:00', '18:00')
            ->days([1, 2, 3, 4, 5])
            ->before(function () {
                \Log::info('tickets:notify-urgent is about to run');
            });
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
