<x-filament-panels::page>
    <div>
        @livewire(\Wallo\FilamentCompanies\Http\Livewire\UpdateCompanyNameForm::class, compact('company'))

        @livewire(\App\Livewire\Company\UpdateCompanyDetailsForm::class, compact('company'))

        @livewire(\Wallo\FilamentCompanies\Http\Livewire\CompanyEmployeeManager::class, compact('company'))
      
        @if (!$company->personal_company && Gate::check('delete', $company))
            <x-filament-companies::section-border />
            @livewire(\Wallo\FilamentCompanies\Http\Livewire\DeleteCompanyForm::class, compact('company'))
        @endif
    </div>
</x-filament-panels::page>
