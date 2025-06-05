<?php

namespace App\Filament\Company\Pages;

use App\Models\Company;
use Exception;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Wallo\FilamentCompanies\Events\AddingCompany;
use Wallo\FilamentCompanies\FilamentCompanies;
use Wallo\FilamentCompanies\Pages\Company\CreateCompany as FilamentCreateCompany;

class CreateCompany extends FilamentCreateCompany
{
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string
    {
        return 'Create Research Group';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Research Group')
                        ->schema(Company::getInfoForm()),

                    Wizard\Step::make('Contact Info')
                        ->schema(Company::getGroupLeaderForm()),

                    Wizard\Step::make('Research Focus')
                        ->schema(Company::getResearchForm()),
                ]),
            ])
            ->statePath('data')
            ->model(FilamentCompanies::companyModel());
    }

    protected function handleRegistration(array $data): Model
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            Gate::forUser($user)->authorize('create', FilamentCompanies::newCompanyModel());

            AddingCompany::dispatch($user);

            $personalCompany = $user?->personalCompany() === null;
            $name = $data['name'] ?? 'Unnamed Research Group';

            // Create the research group
            $company = $user?->ownedCompanies()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'search_slug' => Str::slug($name),
                'description' => $data['description'] ?? null,
                'reference' => str()->random(7),
                'personal_company' => $personalCompany,
                
                // Faculty & Institute
                'faculty' => $data['faculty'] ?? null,
                'institute' => $data['institute'] ?? null,
                
                // Group Leader
                'leader_name' => $data['leader_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'office_address' => $data['office_address'] ?? null,
                'website' => $data['website'] ?? null,
                'orcid' => $data['orcid'] ?? null,
                
                // Research Focus
                'research_keywords' => $data['research_keywords'] ?? null,
                'research_description' => $data['research_description'] ?? null,
                'funding_sources' => $data['funding_sources'] ?? null,
                'preferred_language' => $data['preferred_language'] ?? 'english',
                
                // ELN Information
                'uses_eln' => $data['uses_eln'] ?? false,
                'eln_system' => $data['eln_system'] ?? null,
                'eln_other' => $data['eln_other'] ?? null,
            ]);

            $user?->switchCompany($company);
            $this->companyCreated($name);

            // Define the folder names to be created for research data
            $folderNames = [
                'rawfiles',
                'instructions',
                'literature-assets',
            ];

            foreach ($folderNames as $folderName) {
                MediaLibraryFolder::create([
                    'name' => $folderName,
                    'company_id' => $company->id,
                ]);
            }

            DB::commit();

            return $company;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in research group registration: '.$e->getMessage());
            throw $e;
        }
    }
}
