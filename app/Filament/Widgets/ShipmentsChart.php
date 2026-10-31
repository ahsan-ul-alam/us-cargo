<?php

namespace App\Filament\Widgets;

use App\Models\Shipment;
use Filament\Widgets\ChartWidget;

class ShipmentsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Shipments — last 30 days';

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $counts = Shipment::where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as d, count(*) as c')
            ->groupBy('d')->pluck('c', 'd');

        $labels = [];
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('j M');
            $data[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'New shipments',
                'data' => $data,
                'borderColor' => '#fd9230',
                'backgroundColor' => 'rgba(253,146,48,.15)',
                'fill' => true,
                'tension' => .35,
                'pointRadius' => 0,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
