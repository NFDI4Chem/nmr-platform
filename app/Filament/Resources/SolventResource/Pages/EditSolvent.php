<?php

namespace App\Filament\Resources\SolventResource\Pages;

use App\Filament\Resources\SolventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolvent extends EditRecord
{
    protected static string $resource = SolventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
