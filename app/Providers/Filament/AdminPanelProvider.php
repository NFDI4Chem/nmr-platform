<?php

namespace App\Providers\Filament;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use RalphJSmit\Filament\MediaLibrary\FilamentMediaLibrary;
use Stephenjude\FilamentDebugger\DebuggerPlugin;

class AdminPanelProvider extends PanelProvider
{
    protected static ?string $title = 'Control panel';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('control-panel')
            ->colors([
                'primary' => Color::Gray,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                DebuggerPlugin::make(),
                FilamentExceptionsPlugin::make(),
                AdvancedTablesPlugin::make()
                    ->userViewsEnabled(false)
                    ->resourceEnabled(false),
                FilamentMediaLibrary::make()
                    ->pageTitle('File Browser')
                    ->navigationLabel('File Browser')
                    ->navigationIcon('heroicon-o-folder')
                    ->diskVisibilityPrivate()
                    ->navigationGroup('')
                    ->conversionThumb(enabled: true, width: 600, height: 600)
                    ->mediaPickerModalWidth('7xl')
                    ->acceptPdf(),

            ])
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\DevicesOverviewWidget::class,
                \App\Filament\Widgets\SamplesByStatusChart::class,
                \App\Filament\Widgets\SamplesTrendChart::class,
                \App\Filament\Widgets\RecentSamplesWidget::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Group')
                    ->icon('heroicon-o-building-office')
                    ->url(static function () {
                        $user = Auth::user();
                        $company = $user?->allCompanies()?->first();
                        if ($company) {
                            return route('filament.groups.pages.dashboard', ['tenant' => $company]);
                        }

                        return '#';
                    })
                    ->visible(static fn () => Auth::user()?->allCompanies()?->count() > 0),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->brandLogo(asset('img/logo.svg'))
            ->brandLogoHeight('3rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/company/theme.css')
            ->renderHook(
                'panels::body.end',
                fn (): string => (string) view('components.custom-includes')
            );
    }
}
