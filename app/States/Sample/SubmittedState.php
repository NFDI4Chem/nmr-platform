<?php

namespace App\States\Sample;

use Filament\Support\Contracts\HasLabel;

class SubmittedState extends SampleState implements HasLabel
{
    public static $name = 'submitted';

    public function getLabel(): string
    {
        return __('Submit');
    }

    // public function color(): string
    // {
    //     return 'green';
    // }
}
