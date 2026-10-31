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
            'pending' => '#94a3b8', 'collected' => '#6366f1', 'in_transit' => '#0ea5e9',
            'customs_clearance' => '#f59e0b', 'out_for_delivery' => '#14b8a6',
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
            'labels' => $statuses->map(fn ($s) => str($s)->replace('_', ' ')->title()->toString())->values()->all(),
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
