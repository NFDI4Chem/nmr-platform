<?php

namespace App\Filament\Company\Widgets;

use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GroupSamplesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant) {
            return [];
        }

        $companyId = $tenant->getKey();
        
        $totalSamples = Sample::where('company_id', $companyId)->count();
        $submittedSamples = Sample::where('company_id', $companyId)
            ->where('status', 'submitted')
            ->count();
        $approvedSamples = Sample::where('company_id', $companyId)
            ->where('status', 'approved')
            ->count();
        $completedSamples = Sample::where('company_id', $companyId)
            ->where('status', 'completed')
            ->count();
        $rejectedSamples = Sample::where('company_id', $companyId)
            ->where('status', 'rejected')
            ->count();
        
        // Get last 7 days sample count for the chart
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Sample::where('company_id', $companyId)
                ->whereDate('created_at', $date)
                ->count();
            $last7Days[] = $count;
        }
        
        return [
            Stat::make('Total Samples', $totalSamples)
                ->description('All samples in this group')
                ->descriptionIcon('heroicon-o-beaker')
                ->color('primary')
                ->chart($last7Days),
            
            Stat::make('Submitted', $submittedSamples)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
            
            Stat::make('Approved', $approvedSamples)
                ->description('Ready for analysis')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
            
            Stat::make('Completed', $completedSamples)
                ->description('Analysis finished')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Rejected', $rejectedSamples)
                ->description('Requires attention')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}

