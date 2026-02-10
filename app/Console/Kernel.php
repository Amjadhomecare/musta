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
          
         $schedule->command('maids:reset-booked')->dailyAt('21:00')->timezone('Asia/Dubai');
         $schedule->command('recursions:daily')->dailyAt('22:00')->timezone('Asia/Dubai');

        $schedule->command('report:onclick-monthly')
            // ->everyMinute()
            // ->timezone('Asia/Dubai');
            ->monthlyOn(3, '10:00')
            ->timezone('Asia/Dubai')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('report:onclick-daily')
            ->dailyAt('21:30')
            ->timezone('Asia/Dubai')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

         
        $schedule->command('report:comparative-trial-3ends-email')
            ->monthlyOn(7, '10:00')
            ->timezone('Asia/Dubai')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();


        $schedule->command('report:income-3months-email')
          
            // ->everyMinute()
            // ->timezone('Asia/Dubai');
            ->monthlyOn(12, '10:05')   
            ->timezone('Asia/Dubai')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // Direct Debit follow-up for rejected mandates (RR01 signature issues)
        $schedule->command('dd:process-follow-ups')
            ->dailyAt('10:30')
            ->timezone('Asia/Dubai')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

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
