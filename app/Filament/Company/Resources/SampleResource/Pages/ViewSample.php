<?php

namespace App\Filament\Company\Resources\SampleResource\Pages;

use App\Filament\Company\Resources\SampleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSample extends ViewRecord
{
    protected static string $resource = SampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

