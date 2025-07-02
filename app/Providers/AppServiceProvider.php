<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\App;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->can(['view_any_exception']);
        });

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->modalWidth('sm')
                ->slideOver()
                ->icons([
                    'admin' => 'heroicon-s-cog',
                    'groups' => 'heroicon-s-building-office-2',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Control Panel',
                    'groups' => 'Group Dashboard',
                ])
                ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                    'super_admin',
                    'application_support',
                    'application_dev',
                ]));
        });

        if (App::environment('production') || App::environment('development')) {
            \URL::forceScheme('https');
        }

    }
}
