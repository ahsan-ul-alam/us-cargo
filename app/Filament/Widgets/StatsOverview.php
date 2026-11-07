<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $thisMonth = Shipment::where('created_at', '>=', $monthStart)->count();
        $lastMonth = Shipment::whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), $monthStart])->count();
        $trend = $lastMonth > 0
            ? sprintf('%+d%% vs last month', round(($thisMonth - $lastMonth) / $lastMonth * 100))
            : 'Last month: ' . $lastMonth;

        $revenue = (float) Shipment::where('paid_at', '>=', $monthStart)->sum('paid_amount');
        $outstanding = (float) Shipment::where('payment_status', '!=', 'paid')
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->selectRaw('sum(coalesce(price_gbp, 0) - coalesce(paid_amount, 0)) as t')->value('t');

        // last 7 days, oldest first, for the sparkline
        $spark = collect(range(6, 0))->map(
            fn ($d) => Shipment::whereDate('created_at', now()->subDays($d)->toDateString())->count()
        )->all();

        return [
            Stat::make('Shipments this month', $thisMonth)
                ->description($trend)
                ->descriptionIcon($thisMonth >= $lastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($spark)
                ->color($thisMonth >= $lastMonth ? 'success' : 'danger'),

            Stat::make('Active shipments', Shipment::whereNotIn('status', ['pending', 'delivered', 'returned', 'cancelled'])->count())
                ->description('Collected → in transit')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Revenue this month', '£' . number_format($revenue, 2))
                ->description('Payments received')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Outstanding balance', '£' . number_format(max(0, $outstanding), 2))
                ->description('Unpaid / part-paid shipments')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($outstanding > 0 ? 'warning' : 'gray'),

            Stat::make('New quote requests', QuoteRequest::where('status', 'new')->count())
                ->description('Awaiting response')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('danger'),

            Stat::make('Unread messages', ContactMessage::where('status', 'unread')->count())
                ->description('From the contact form')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('gray'),

            Stat::make('Delivered', Shipment::where('status', 'delivered')->count())
                ->description('All time')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Customers', Customer::count())
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
