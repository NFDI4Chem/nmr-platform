<?php

namespace App\Filament\Company\Resources\SampleResource\Pages;

use App\Filament\Company\Resources\SampleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSample extends CreateRecord
{
    protected static string $resource = SampleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the company_id and user_id
        $tenant = Filament::getTenant();
        if ($tenant) {
            $data['company_id'] = $tenant->getKey();
        }
        $data['user_id'] = auth()->id();

        // Set default status if not set
        if (! isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'submitted';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
