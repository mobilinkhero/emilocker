<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue (PKR)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i));

        return [
            'datasets' => [[
                'label'           => 'Revenue',
                'data'            => $months->map(fn($month) =>
                    Payment::where('status', 'completed')
                        ->whereYear('paid_at', $month->year)
                        ->whereMonth('paid_at', $month->month)
                        ->sum('total_paid')
                )->toArray(),
                'backgroundColor' => '#10b981',
                'borderColor'     => '#059669',
            ]],
            'labels' => $months->map(fn($m) => $m->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string { return 'bar'; }
}
