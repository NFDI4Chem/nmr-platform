<?php

namespace App\Filament\Resources\SampleResource\Pages;

use App\Filament\Resources\SampleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSample extends CreateRecord
{
    protected static string $resource = SampleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Sample created successfully';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate sample reference if not provided
        if (empty($data['reference'])) {
            $data['reference'] = 'NMR-'.date('Ym').'-ID-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
