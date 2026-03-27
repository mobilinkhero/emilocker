<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Widgets\ChartWidget;

class DeviceStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Device Status Distribution';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [[
                'data'            => [
                    Device::where('status', 'active')->count(),
                    Device::where('status', 'locked')->count(),
                    Device::where('status', 'kiosk')->count(),
                    Device::where('status', 'wiped')->count(),
                ],
                'backgroundColor' => ['#10b981', '#ef4444', '#f59e0b', '#6b7280'],
            ]],
            'labels' => ['Active', 'Locked', 'Kiosk', 'Wiped'],
        ];
    }

    protected function getType(): string { return 'doughnut'; }
}
