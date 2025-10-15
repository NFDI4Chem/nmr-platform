<?php

namespace App\Filament\Company\Resources\MoleculeResource\Pages;

use App\Filament\Company\Resources\MoleculeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMolecules extends ListRecords
{
    protected static string $resource = MoleculeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - molecules are created through samples
        ];
    }
}

