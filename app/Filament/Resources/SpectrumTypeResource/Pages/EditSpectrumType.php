<?php

namespace App\Filament\Resources\SpectrumTypeResource\Pages;

use App\Filament\Resources\SpectrumTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpectrumType extends EditRecord
{
    protected static string $resource = SpectrumTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
