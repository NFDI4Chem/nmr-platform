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
use App\Filament\Company\Pages\CreateCompany;
use App\Models\Company;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use RalphJSmit\Filament\MediaLibrary\FilamentMediaLibrary;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Wallo\FilamentCompanies\FilamentCompanies;
use Wallo\FilamentCompanies\Pages\Auth\Register;
use Wallo\FilamentCompanies\Pages\Company\CompanySettings;

class FilamentCompaniesServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('groups')
            ->path('group')
            ->default()
            ->passwordReset()
            ->homeUrl(static fn (): string => Auth::user()?->companies?->first()
                ? url(Pages\Dashboard::getUrl(panel: 'group', tenant: Auth::user()->companies->first()))
                : url(Pages\Dashboard::getUrl(panel: 'group')))
            ->registration(Register::class)
            ->tenant(Company::class)
            ->tenantProfile(CompanySettings::class)
            ->tenantRegistration(CreateCompany::class)
            ->plugins(
                [
                    FilamentCompanies::make()
                        ->switchCurrentCompany()
                        ->companies(invitations: true)
                        ->notifications()
                        ->modals(),
                    AdvancedTablesPlugin::make()
                        ->userViewsEnabled(false)
                        ->resourceEnabled(false)
                        ->tenant(Company::class),
                    FilamentMediaLibrary::make()
                        ->pageTitle('File Browser')
                        ->navigationLabel('File Browser')
                        ->navigationIcon('heroicon-o-folder')
                        ->diskVisibilityPrivate()
                        ->mediaPickerModalWidth('7xl')
                        ->conversionThumb(enabled: true, width: 600, height: 600)
                        ->acceptPdf(),
                ])
            ->colors([
                'primary' => Color::Gray,
            ])
            ->discoverResources(in: app_path('Filament/Company/Resources'), for: 'App\\Filament\\Company\\Resources')
            ->discoverPages(in: app_path('Filament/Company/Pages'), for: 'App\\Filament\\Company\\Pages')
            ->discoverClusters(in: app_path('Filament/Company/Clusters'), for: 'App\\Filament\\Company\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->userMenuItems([
                'api-tokens' => MenuItem::make()
                    ->label(static fn (): string => __('filament-companies::default.navigation.links.tokens'))
                    ->icon('heroicon-o-key')
                    ->url(static fn () => '/user/api-tokens'),
                'logout' => MenuItem::make()
                    ->label('Sign out')
                    ->url(static fn () => '/logout'),
            ])
            ->authGuard('web')
            ->discoverWidgets(in: app_path('Filament/Company/Widgets'), for: 'App\\Filament\\Company\\Widgets')
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
                fn (): string => (string) view('components.tawk-chat')
            );
    }

    protected function registerFilamentNavigation()
    {
        Filament::serving(function () {
            static $registered = false;
            if (! $registered) {
                Filament::registerUserMenuItems([
                    MenuItem::make()
                        ->label('Profile')
                        ->url('/user/profile')
                        ->icon('heroicon-s-cog'),
                ]);
                $registered = true;
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        if (! $this->app->runningInConsole()) {
            $this->registerFilamentNavigation();
        }

        FilamentCompanies::createUsersUsing(CreateNewUser::class);
        // FilamentCompanies::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        FilamentCompanies::updateUserPasswordsUsing(UpdateUserPassword::class);

        FilamentCompanies::createCompaniesUsing(CreateCompany::class);
        FilamentCompanies::updateCompanyNamesUsing(UpdateCompanyName::class);
        FilamentCompanies::addCompanyEmployeesUsing(AddCompanyEmployee::class);
        FilamentCompanies::inviteCompanyEmployeesUsing(InviteCompanyEmployee::class);
        FilamentCompanies::removeCompanyEmployeesUsing(RemoveCompanyEmployee::class);
        FilamentCompanies::deleteCompaniesUsing(DeleteCompany::class);
        FilamentCompanies::deleteUsersUsing(DeleteUser::class);

        MediaLibraryItem::creating(function (MediaLibraryItem $mediaLibraryItem) {
            $mediaLibraryItem->company_id ??= Filament::getTenant() ? Filament::getTenant()->getKey() : null;
        });

        MediaLibraryItem::addGlobalScope('tenant', function (Builder $query) {
            return filament()->getTenant() ? $query->where('company_id', filament()->getTenant()->getKey()) : null;
        });

        MediaLibraryFolder::creating(function (MediaLibraryFolder $mediaLibraryFolder) {
            $mediaLibraryFolder->company_id ??= Filament::getTenant() ? Filament::getTenant()->getKey() : null;
        });

        MediaLibraryFolder::addGlobalScope('tenant', function (Builder $query) {
            return filament()->getTenant() ? $query->where('company_id', filament()->getTenant()->getKey()) : null;
        });
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        FilamentCompanies::defaultApiTokenPermissions(['read']);

        FilamentCompanies::role('pi', 'Principal Investigator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Principal Investigators can perform any action and have overall responsibility for the research group.');

        FilamentCompanies::role('postdoc', 'Postdoctoral Researcher', [
            'create',
            'read',
            'update',
        ])->description('Postdoctoral Researchers can read, create, and update research projects and publications.');

        FilamentCompanies::role('grad_student', 'Graduate Student', [
            'create',
            'read',
            'update',
        ])->description('Graduate Students can read, create, and update research tasks and their own theses.');

        FilamentCompanies::role('research_assistant', 'Research Assistant', [
            'create',
            'read',
            'update',
        ])->description('Research Assistants can read, create, and update experiments and data collection.');

        FilamentCompanies::role('undergrad_student', 'Undergraduate Student', [
            'read',
            'create',
        ])->description('Undergraduate Students can read and assist in creating basic research tasks.');

        FilamentCompanies::role('project_manager', 'Project Manager', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Project Managers can perform any action related to managing project timelines and administrative tasks.');

        FilamentCompanies::role('admin_support', 'Administrative Support', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrative Support can perform any administrative tasks to assist the research group.');

        FilamentCompanies::role('collaborator', 'Collaborator', [
            'read',
            'create',
            'update',
        ])->description('Collaborators can read, create, and update joint research efforts and shared data.');

        FilamentCompanies::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

    }
}
