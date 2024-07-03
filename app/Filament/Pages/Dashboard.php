<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Control Panel';

    protected static string $view = 'filament.pages.dashboard';
}
