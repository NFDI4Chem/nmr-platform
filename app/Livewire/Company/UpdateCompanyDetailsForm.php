<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Wallo\FilamentCompanies\Events\CompanyUpdated;
use Wallo\FilamentCompanies\FilamentCompanies;

class UpdateCompanyDetailsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    /**
     * The company instance.
     */
    public mixed $company;

    /**
     * Mount the component.
     */
    public function mount(mixed $company): void
    {
        $this->company = $company;

        $this->form->fill($this->company->toArray());
    }

    public function form(Form $form): Form
    {
        $company_form = Company::getInfoForm();

        // Add Group Leader and Research forms
        $company_form = array_merge($company_form, Company::getGroupLeaderForm());
        $company_form = array_merge($company_form, Company::getResearchForm());

        return $form
            ->schema(
                $company_form
            )
            ->operation('edit')
            ->statePath('data')
            ->model(FilamentCompanies::companyModel());
    }

    public function updateCompanyProfile(): void
    {
        $user = Auth::user();
        $data = $this->data;

        Gate::forUser($user)->authorize('update', $this->company);

        CompanyUpdated::dispatch($this->company);

        /** @var Company $company */
        $this->company->update([
            // Basic Info
            'name' => $data['name'] ?? $this->company->name,
            'description' => $data['description'] ?? $this->company->description,
            'slug' => Str::slug($data['name'] ?? $this->company->name),
            'search_slug' => Str::slug($data['name'] ?? $this->company->name),

            // Faculty & Institute
            'faculty' => $data['faculty'] ?? $this->company->faculty,
            'institute' => $data['institute'] ?? $this->company->institute,

            // ELN Information
            'uses_eln' => $data['uses_eln'] ?? $this->company->uses_eln,
            'eln_system' => $data['eln_system'] ?? $this->company->eln_system,
            'eln_other' => $data['eln_other'] ?? $this->company->eln_other,

            // Group Leader
            'leader_name' => $data['leader_name'] ?? $this->company->leader_name,
            'email' => $data['email'] ?? $this->company->email,
            'phone' => $data['phone'] ?? $this->company->phone,
            'office_address' => $data['office_address'] ?? $this->company->office_address,
            'website' => $data['website'] ?? $this->company->website,
            'orcid' => $data['orcid'] ?? $this->company->orcid,

            // Research Focus
            'research_keywords' => $data['research_keywords'] ?? $this->company->research_keywords,
            'research_description' => $data['research_description'] ?? $this->company->research_description,
            'funding_sources' => $data['funding_sources'] ?? $this->company->funding_sources,
            'preferred_language' => $data['preferred_language'] ?? $this->company->preferred_language,
        ]);

        // Handle logo if provided
        if (isset($data['logo_id'])) {
            $this->company->logo_id = $data['logo_id'];
            $this->company->save();
        }

        $this->getUpdatedNotification()->send();
    }

    protected function getUpdatedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Company profile updated'));
    }

    public function render()
    {
        return view('livewire.company.update-company-details-form');
    }
}

