<?php

namespace App\Filament\Resources\MoleculeResource\Pages;

use App\Filament\Resources\MoleculeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMolecule extends CreateRecord
{
    protected static string $resource = MoleculeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Molecule created successfully';
    }
}
