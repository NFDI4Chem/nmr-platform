<?php

namespace App\Models;

use Archilex\AdvancedTables\Concerns\HasViews;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
// use Illuminate\Contracts\Auth\MustVerifyEmail
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Wallo\FilamentCompanies\HasCompanies;

class User extends Authenticatable implements Auditable, FilamentUser, HasAvatar, HasDefaultTenant, HasTenants
{
    use HasApiTokens;
    use HasCompanies;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use HasViews;
    use Notifiable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'push_notifications_enabled',
        'sound_notifications_enabled',
        'email_notifications_enabled',
        'theme_mode',
        'preferred_language',
        'pin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define the relationship with linked social accounts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    /**
     * Check if user can access a particular panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->roles()->exists();
        }

        return true;
    }

    /**
     * Check if user can access a particular tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->belongsToCompany($tenant);
    }

    /**
     * Get the list of tenants for the user.
     */
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->allCompanies();
    }

    /**
     * Get the default tenant for the user.
     */
    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->currentCompany;
    }

    /**
     * Get the avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): string
    {
        return $this->profile_photo_url;
    }
}
