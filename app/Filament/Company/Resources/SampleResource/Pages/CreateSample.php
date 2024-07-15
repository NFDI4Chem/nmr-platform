<?php

namespace App\Filament\Company\Resources\SampleResource\Pages;

use App\Filament\Company\Resources\SampleResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Sample;

class CreateSample extends CreateRecord
{
    protected static string $resource = SampleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return Sample::mutateFormData($data);
    }
}
