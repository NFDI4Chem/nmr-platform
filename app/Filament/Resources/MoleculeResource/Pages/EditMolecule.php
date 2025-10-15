<?php

namespace App\Filament\Resources\MoleculeResource\Pages;

use App\Filament\Resources\MoleculeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMolecule extends EditRecord
{
    protected static string $resource = MoleculeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Molecule updated successfully';
    }
}
