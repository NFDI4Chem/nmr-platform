<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MolDownload extends Field
{
    protected string $view = 'forms.components.mol-download';

    public function mount($record)
    {
        $this->record = $record;
    }
}
