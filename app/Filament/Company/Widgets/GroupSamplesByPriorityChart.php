<?php

namespace App\Filament\Company\Widgets;

use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GroupSamplesByPriorityChart extends ChartWidget
{
    protected static ?string $heading = 'Samples by Priority';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';
    
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $companyId = $tenant->getKey();

        $samplesData = Sample::select('priority', DB::raw('count(*) as count'))
            ->where('company_id', $companyId)
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        if (empty($samplesData)) {
            return [
                'datasets' => [[
                    'label' => 'Samples',
                    'data' => [0],
                    'backgroundColor' => ['rgba(200, 200, 200, 0.5)'],
                ]],
                'labels' => ['No Data'],
            ];
        }

        // Order by priority
        $orderedData = [];
        $orderedLabels = [];
        $colors = [
            'LOW' => 'rgba(156, 163, 175, 0.5)',    // gray
            'MEDIUM' => 'rgba(59, 130, 246, 0.5)',  // blue
            'HIGH' => 'rgba(251, 146, 60, 0.5)',    // orange
        ];
        $borderColors = [
            'LOW' => 'rgb(156, 163, 175)',
            'MEDIUM' => 'rgb(59, 130, 246)',
            'HIGH' => 'rgb(251, 146, 60)',
        ];

        foreach (['LOW', 'MEDIUM', 'HIGH'] as $priority) {
            if (isset($samplesData[$priority])) {
                $orderedData[] = $samplesData[$priority];
                $orderedLabels[] = $priority;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Samples',
                    'data' => $orderedData,
                    'backgroundColor' => array_map(fn($label) => $colors[$label], $orderedLabels),
                    'borderColor' => array_map(fn($label) => $borderColors[$label], $orderedLabels),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $orderedLabels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}

