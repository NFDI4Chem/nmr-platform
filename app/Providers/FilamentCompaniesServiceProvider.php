<?php

namespace App\Providers;

use App\Actions\FilamentCompanies\AddCompanyEmployee;
use App\Actions\FilamentCompanies\CreateNewUser;
use App\Actions\FilamentCompanies\DeleteCompany;
use App\Actions\FilamentCompanies\DeleteUser;
use App\Actions\FilamentCompanies\InviteCompanyEmployee;
use App\Actions\FilamentCompanies\RemoveCompanyEmployee;
use App\Actions\FilamentCompanies\UpdateCompanyName;
use App\Actions\FilamentCompanies\UpdateUserPassword;
use App\Actions\FilamentCompanies\UpdateUserProfileInformation;
use App\Models\Company;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
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
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Wallo\FilamentCompanies\FilamentCompanies;
use Wallo\FilamentCompanies\Pages\Auth\Register;
use Wallo\FilamentCompanies\Pages\Company\CompanySettings;
use Wallo\FilamentCompanies\Pages\Company\CreateCompany;
use RalphJSmit\Filament\MediaLibrary\FilamentMediaLibrary;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class FilamentCompaniesServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('company')
            ->path('company')
            ->default()
            ->passwordReset()
            ->homeUrl(static fn (): string => url(Pages\Dashboard::getUrl(panel: 'company', tenant: Auth::user()?->personalCompany())))
            ->plugins(
                [
                    FilamentMediaLibrary::make()
                        ->diskVisibilityPrivate()
                        ->navigationIcon('heroicon-o-folder-open')
                        ->navigationLabel('File Browser'),
                    FilamentCompanies::make()
                        ->switchCurrentCompany()
                        ->companies(invitations: true)
                        ->notifications()
                        ->modals(),
                    EnvironmentIndicatorPlugin::make(),
                ]
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->registration(Register::class)
            ->tenant(Company::class)
            ->tenantProfile(CompanySettings::class)
            ->tenantRegistration(CreateCompany::class)
            ->discoverResources(in: app_path('Filament/Company/Resources'), for: 'App\\Filament\\Company\\Resources')
            ->discoverPages(in: app_path('Filament/Company/Pages'), for: 'App\\Filament\\Company\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->userMenuItems([])
            ->userMenuItems([
                MenuItem::make()
                    ->label(static fn (): string => __('filament-companies::default.navigation.links.tokens'))
                    ->icon('heroicon-o-key')
                    ->url(static fn () => '/user/api-tokens'),
            ])
            ->authGuard('web')
            ->discoverWidgets(in: app_path('Filament/Company/Widgets'), for: 'App\\Filament\\Company\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ->brandLogo(asset('img/logo-full.png'))
            ->brandLogoHeight('3rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/company/theme.css')
            ->renderHook(
                'panels::body.end',
                fn (): string => view('components.tawk-chat')
            );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        FilamentCompanies::createUsersUsing(CreateNewUser::class);
        FilamentCompanies::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        FilamentCompanies::updateUserPasswordsUsing(UpdateUserPassword::class);

        FilamentCompanies::createCompaniesUsing(CreateCompany::class);
        FilamentCompanies::updateCompanyNamesUsing(UpdateCompanyName::class);
        FilamentCompanies::addCompanyEmployeesUsing(AddCompanyEmployee::class);
        FilamentCompanies::inviteCompanyEmployeesUsing(InviteCompanyEmployee::class);
        FilamentCompanies::removeCompanyEmployeesUsing(RemoveCompanyEmployee::class);
        FilamentCompanies::deleteCompaniesUsing(DeleteCompany::class);
        FilamentCompanies::deleteUsersUsing(DeleteUser::class);

        MediaLibraryItem::creating(function (MediaLibraryItem $mediaLibraryItem) {
            $mediaLibraryItem->company_id ??= Filament::getTenant()?->getKey();
        });

        MediaLibraryItem::addGlobalScope('tenant', function (Builder $query) {
            return $query->where('company_id', filament()->getTenant()?->getKey());
        });

        MediaLibraryFolder::creating(function (MediaLibraryFolder $mediaLibraryFolder) {
            $mediaLibraryFolder->company_id ??= Filament::getTenant()?->getKey();
        });

        MediaLibraryFolder::addGlobalScope('tenant', function (Builder $query) {
            return $query->where('company_id', filament()->getTenant()?->getKey());
        });
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        FilamentCompanies::defaultApiTokenPermissions(['read']);

        FilamentCompanies::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        FilamentCompanies::role('editor', 'Editor', [
            'read',
            'create',
            'update',
        ])->description('Editor users have the ability to read, create, and update.');
    }
}
