<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        return $greeting . ', ' . (auth()->user()?->name ?? 'there');
    }

    public function getSubheading(): ?string
    {
        return now()->format('l, j F Y') . ' · here is what is happening with Prime Cargo today.';
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
