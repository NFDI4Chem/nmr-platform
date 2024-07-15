<?php

namespace App\Filament\Traits;

use Auth;
use Filament\Facades\Filament;

trait MutatesSampleFormData
{
    protected static function mutateFormData(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        $data['user_id'] = Auth::user()->id;

        return $data;
    }
}
