<?php

namespace App\Filament\Widgets;

use App\Models\Sample;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SamplesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Samples Trend (Last 7 Days)';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $samplesData = Sample::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero counts
        $dates = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');

            $sample = $samplesData->firstWhere('date', $date);
            $counts[] = $sample ? $sample->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Samples Created',
                    'data' => $counts,
                    'fill' => true,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
