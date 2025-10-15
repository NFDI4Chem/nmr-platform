<?php

namespace App\Filament\Widgets;

use App\Models\Sample;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SamplesByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Samples by Status';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $samplesData = Sample::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

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
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(16, 185, 129)',
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
