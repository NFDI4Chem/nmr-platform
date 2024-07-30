<?php

namespace App\Filament\Resources\SolventResource\Pages;

use App\Filament\Resources\SolventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolvents extends ListRecords
{
    protected static string $resource = SolventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
