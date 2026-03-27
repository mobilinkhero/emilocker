<?php

namespace App\Console;

use App\Jobs\AutoLockOverdueDevicesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run every hour — lock devices with overdue EMI
        $schedule->job(new AutoLockOverdueDevicesJob)->hourly();

        // Send payment reminders daily at 10am
        $schedule->job(new \App\Jobs\SendPaymentReminderJob)->dailyAt('10:00');

        // Mark devices offline if no heartbeat in 10 minutes
        $schedule->call(function () {
            \App\Models\Device::where('is_online', true)
                ->where('last_seen_at', '<', now()->subMinutes(10))
                ->update(['is_online' => false]);
        })->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
