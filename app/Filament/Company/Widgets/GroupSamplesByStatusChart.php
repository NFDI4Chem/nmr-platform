<?php

namespace App\Filament\Company\Widgets;

use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GroupSamplesByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Samples by Status';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $companyId = $tenant->getKey();

        $samplesData = Sample::select('status', DB::raw('count(*) as count'))
            ->where('company_id', $companyId)
            ->groupBy('status')
            ->pluck('count', 'status')
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

        return [
            'datasets' => [
                [
                    'label' => 'Samples',
                    'data' => array_values($samplesData),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',  // blue for submitted
                        'rgba(34, 197, 94, 0.5)',   // green for approved
                        'rgba(239, 68, 68, 0.5)',   // red for rejected
                        'rgba(16, 185, 129, 0.5)',  // teal for completed
                        'rgba(156, 163, 175, 0.5)', // gray for draft
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(16, 185, 129)',
                        'rgb(156, 163, 175)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($samplesData)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
