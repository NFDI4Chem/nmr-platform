<?php

namespace App\Filament\Resources\ImpurityResource\Pages;

use App\Filament\Resources\ImpurityResource;
use App\Models\Impurity;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListImpurities extends ListRecords
{
    protected static string $resource = ImpurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge(Impurity::count()),
            
            '1H' => Tab::make('Proton (¹H)')
                ->icon('heroicon-o-beaker')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('nucleus', '1H'))
                ->badge(Impurity::where('nucleus', '1H')->count())
                ->badgeColor('success'),
            
            '13C' => Tab::make('Carbon (¹³C)')
                ->icon('heroicon-o-beaker')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('nucleus', '13C'))
                ->badge(Impurity::where('nucleus', '13C')->count())
                ->badgeColor('info'),
        ];
    }
}

