<?php

namespace App\Filament\Traits;

use App\Models\Ticker;
use Auth;
use Filament\Facades\Filament;

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
        $ticker->index = $ticker->index + 1;
        $ticker->save();

        return $data;
    }
}
