<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use Notifiable;
    use \OwenIt\Auditing\Auditable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User model and LinkedSocialAccount relationship - one to many
     *
     * @var array
     */
    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->roles()->exists() ? true : false;
        }

        return true;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->belongsToCompany($tenant);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->allCompanies();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->currentCompany;
    }

    public function getFilamentAvatarUrl(): string
    {
        return $this->profile_photo_url;
    }
}
