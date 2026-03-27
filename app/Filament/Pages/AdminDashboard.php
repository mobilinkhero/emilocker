<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Models\EmiPlan;
use App\Models\Payment;
use Filament\Pages\Dashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\RevenueChart::class,
            \App\Filament\Widgets\DeviceStatusChart::class,
        ];
    }
}
