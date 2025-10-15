<?php

namespace App\Filament\Company\Widgets;

use App\Filament\Company\Resources\SampleResource;
use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    protected static string $view = 'filament.company.widgets.quick-links-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getActions(): array
    {
        return [
            [
                'label' => 'Create New Sample',
                'icon' => 'heroicon-o-plus-circle',
                'url' => SampleResource::getUrl('create'),
                'color' => 'primary',
                'description' => 'Submit a new sample for analysis',
            ],
            [
                'label' => 'Search Samples',
                'icon' => 'heroicon-o-magnifying-glass',
                'url' => SampleResource::getUrl('index'),
                'color' => 'gray',
                'description' => 'View and search all samples',
            ],
        ];
    }
}

