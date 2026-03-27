<?php

namespace App\Jobs;

use App\Models\EmiPlan;
use App\Services\DeviceCommandService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduled daily — locks devices with overdue EMI payments.
 */
class AutoLockOverdueDevicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DeviceCommandService $commandService): void
    {
        EmiPlan::with('device')
            ->where('status', 'active')
            ->whereDate('next_due_date', '<', now()->subDays(1)) // 1 day grace
            ->chunk(100, function ($plans) use ($commandService) {
                foreach ($plans as $plan) {
                    $device = $plan->device;
                    if ($device && $device->status !== 'locked') {
                        $device->update(['status' => 'locked', 'lock_reason' => 'emi_overdue']);
                        $commandService->sendCommand($device, 'lock', ['reason' => 'emi_overdue']);
                    }
                }
            });
    }
}
