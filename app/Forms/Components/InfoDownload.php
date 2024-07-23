<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class InfoDownload extends Field
{
    protected string $view = 'forms.components.info-download';

    public function mount($record)
    {
        $this->record = $record;
    }
}
