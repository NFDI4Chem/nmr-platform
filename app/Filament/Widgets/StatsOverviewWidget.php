<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Molecule;
use App\Models\Sample;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSamples = Sample::count();
        $pendingSamples = Sample::where('status', 'submitted')->count();
        $completedSamples = Sample::where('status', 'completed')->count();

        return [
            Stat::make('Total Samples', $totalSamples)
                ->description('All samples in the system')
                ->descriptionIcon('heroicon-o-beaker')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Pending Samples', $pendingSamples)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Completed Samples', $completedSamples)
                ->description('Successfully processed')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Total Molecules', Molecule::count())
                ->description('Molecules in database')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),

            Stat::make('Active Devices', Device::where('status', 'ACTIVE')->count())
                ->description('Operational devices')
                ->descriptionIcon('heroicon-o-cpu-chip')
                ->color('success'),

            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('gray'),
        ];
    }
}
