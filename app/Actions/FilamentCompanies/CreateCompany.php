<?php

namespace App\Actions\FilamentCompanies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Wallo\FilamentCompanies\Contracts\CreatesCompanies;
use Wallo\FilamentCompanies\Events\AddingCompany;
use Wallo\FilamentCompanies\FilamentCompanies;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use Illuminate\Support\Str;

class CreateCompany implements CreatesCompanies
{
    /**
     * Validate and create a new company for the given user.
     *
     * @param  array<string, string>  $input
     *
     * @throws AuthorizationException
     */
    public function create(User $user, array $input): Company
    {
        Gate::forUser($user)->authorize('create', FilamentCompanies::newCompanyModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createCompany');

        AddingCompany::dispatch($user);

        $company = $user->ownedCompanies()->create([
            'name' => $input['name'],
            'personal_company' => false,
        ]);

        $user->switchCompany($company);

        // Create required folders

        $folderNames = [
            Str::slug($company->name. ' '. $company->id, '-'),
        ];

        foreach ($folderNames as $folderName) {
            MediaLibraryFolder::create(
                [
                    'name' => $folderName,
                    'company_id' => $company->id,
                ]
            );
        }

        return $company;
    }
}
