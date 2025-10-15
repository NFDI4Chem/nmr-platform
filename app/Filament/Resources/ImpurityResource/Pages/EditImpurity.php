<?php

namespace App\Filament\Resources\ImpurityResource\Pages;

use App\Filament\Resources\ImpurityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImpurity extends EditRecord
{
    protected static string $resource = ImpurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
