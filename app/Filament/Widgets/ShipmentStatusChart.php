<?php

namespace App\Filament\Widgets;

use App\Models\Shipment;
use Filament\Widgets\ChartWidget;

class ShipmentStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Shipments by status';

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $colors = [
            'pending' => '#94a3b8', 'collected' => '#6366f1',
            'uk_airport' => '#8b5cf6', 'in_air' => '#0ea5e9', 'bd_airport' => '#f59e0b', 'sylhet_airport' => '#eab308',
            'uk_port' => '#a855f7', 'at_sea' => '#0284c7', 'bd_port' => '#d97706',
            'dhaka_office' => '#14b8a6', 'sylhet_office' => '#0d9488', 'in_transit' => '#06b6d4',
            'delivered' => '#22c55e', 'returned' => '#f97316', 'cancelled' => '#ef4444',
        ];

        $counts = Shipment::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $statuses = collect($colors)->keys()->filter(fn ($s) => ($counts[$s] ?? 0) > 0);

        return [
            'datasets' => [[
                'data' => $statuses->map(fn ($s) => (int) $counts[$s])->values()->all(),
                'backgroundColor' => $statuses->map(fn ($s) => $colors[$s])->values()->all(),
                'borderWidth' => 0,
            ]],
            'labels' => $statuses->map(fn ($s) => Shipment::allStatusOptions()[$s] ?? $s)->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'right']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
            'cutout' => '62%',
        ];
    }
}
