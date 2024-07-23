<?php

namespace App\Filament\Company\Resources\SampleResource\Pages;

use App\Filament\Company\Resources\SampleResource;
use App\Models\Sample;
use Auth;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;

class CreateSample extends CreateRecord
{
    protected static string $resource = SampleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return Sample::mutateFormData($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $sample = static::getModel()::create($data);
        $this->form->model($sample)->saveRelationships();

        return $sample;
    }

    protected function afterCreate(): void
    {
        $sample = $this->record->fresh();

        $sample_folder = MediaLibraryFolder::where([
            ['name', $sample->reference],
            ['company_id', Filament::getTenant()->id],
        ])->first();

        // Check if sample_folder is found
        if (! $sample_folder) {
            // Handle the case where the folder is not found
            // e.g., throw an exception or log an error
            throw new \Exception('Sample folder not found.');
        }

        $files = $sample->getMedia('*');

        // Check if there are any files to process
        if (count($files) > 0) {
            foreach ($files as $file) {
                MediaLibraryItem::create([
                    'uploaded_by_user_id' => Auth::user()->id,
                    'company_id' => Filament::getTenant()->id,
                    'user_id' => Auth::user()->id,
                    'folder_id' => $sample_folder->id,
                ]);
            }
        }
    }
}
