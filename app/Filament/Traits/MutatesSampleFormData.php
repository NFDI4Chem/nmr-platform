<?php

namespace App\Filament\Traits;

use App\Models\Ticker;
use Auth;
use Filament\Facades\Filament;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;

trait MutatesSampleFormData
{
    protected static function mutateFormData(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        $data['user_id'] = Auth::user()->id;
        $ticker = Ticker::where('type', 'App\Models\Sample')->first();
        $data['ticker_id'] = $ticker->index;
        $data['personal_key'] = $data['reference'];
        $data['reference'] = 'NMR-'.date('Ym').'-'.$ticker->index.'-'.$data['reference'];

        $group_folder_slug = Filament::getTenant()->slug.'-'.Filament::getTenant()->reference;
        $group_folder = MediaLibraryFolder::where([
            ['name', $group_folder_slug],
            ['company_id', Filament::getTenant()->id],
        ])->first();

        $sample_folder = MediaLibraryFolder::firstOrCreate(
            [
                'name' => $data['reference'],
                'company_id' => $data['company_id'],
                'user_id' => $data['user_id'],
                'parent_id' => $group_folder->id,
            ],
            []
        );

        $ticker->index = $ticker->index + 1;
        $ticker->save();

        return $data;
    }
}
