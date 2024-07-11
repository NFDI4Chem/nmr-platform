<?php

namespace App\Filament\Company\Pages;

use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use Wallo\FilamentCompanies\Events\AddingCompany;
use Wallo\FilamentCompanies\FilamentCompanies;
use Wallo\FilamentCompanies\Pages\Company\CreateCompany as FilamentCreateCompany;

class CreateCompany extends FilamentCreateCompany
{
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Group Information')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(400)
                                ->live()
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Please enter the full legal name of your business as it will appear on all official documents and communications.'),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->hidden()
                                ->maxLength(400),

                        ]),
                ]),
            ])
            ->model(FilamentCompanies::companyModel())
            ->statePath('data');
    }

    protected function handleRegistration(array $data): Model
    {
        $user = Auth::user();

        Gate::forUser($user)->authorize('create', FilamentCompanies::newCompanyModel());

        AddingCompany::dispatch($user);

        $personalCompany = $user?->personalCompany() === null;

        /** @var Company $company */
        $company = $user?->ownedCompanies()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'personal_company' => $personalCompany,
        ]);

        $user?->switchCompany($company);

        $folderNames = [
            Str::slug($company->name.' '.$company->id, '-'),
        ];

        foreach ($folderNames as $folderName) {
            MediaLibraryFolder::create(
                [
                    'name' => $folderName,
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ]
            );
        }

        $name = $data['name'];

        $this->companyCreated($name);

        return $company;
    }
}
