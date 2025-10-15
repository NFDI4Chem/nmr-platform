<?php

namespace App\Filament\Resources\SpectrumTypeResource\Pages;

use App\Filament\Resources\SpectrumTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpectrumType extends CreateRecord
{
    protected static string $resource = SpectrumTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Spectrum Type created successfully';
    }
}
