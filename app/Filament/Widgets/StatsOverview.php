<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\EmiPlan;
use App\Models\Payment;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Devices', Device::count())
                ->description('All registered devices')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('primary'),

            Stat::make('Active Devices', Device::where('status', 'active')->count())
                ->description('Currently unlocked')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Locked Devices', Device::where('status', 'locked')->count())
                ->description('EMI overdue or manual lock')
                ->icon('heroicon-o-lock-closed')
                ->color('danger'),

            Stat::make('Overdue EMIs', EmiPlan::where('status', 'active')->whereDate('next_due_date', '<', now())->count())
                ->description('Payments past due date')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Total Customers', Customer::where('is_active', true)->count())
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Revenue This Month',
                'PKR ' . number_format(
                    Payment::where('status', 'completed')
                        ->whereMonth('paid_at', now()->month)
                        ->sum('total_paid'), 0
                ))
                ->description('Collected this month')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
