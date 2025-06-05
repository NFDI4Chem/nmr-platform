<?php

namespace App\Models;

use App\Services\DocumentDefaultService;
use Filament\Forms;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Wallo\FilamentCompanies\Company as FilamentCompaniesCompany;
use Wallo\FilamentCompanies\Events\CompanyCreated;
use Wallo\FilamentCompanies\Events\CompanyDeleted;
use Wallo\FilamentCompanies\Events\CompanyUpdated;

class Company extends FilamentCompaniesCompany implements HasAvatar, HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected static function booted()
    {
        static::created(function ($company) {
            $documentDefaultService = new DocumentDefaultService;
            $documentDefaultService->createDefaultsForCompany($company->id);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name', // Research Group Name
        'personal_company',
        'description',
        'slug',
        'search_slug',

        // Faculty & Institute
        'faculty',
        'institute',

        // Group Leader (Principal Investigator)
        'leader_name',
        'email', 
        'phone',
        'office_address',
        'website',
        'orcid',

        // Research Focus
        'research_keywords',
        'research_description',

        // Administrative
        'funding_sources',
        'preferred_language',
        
        // ELN Information
        'uses_eln',
        'eln_system',
        'eln_other',
        
        'reference',
    ];

    protected $encryptable = [
        'account_number',
        'sort_code',
        'iban',
        'swift_bic',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => CompanyCreated::class,
        'updated' => CompanyUpdated::class,
        'deleted' => CompanyDeleted::class,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'state_id' => 'integer',

        'personal_company' => 'boolean',
    ];

    public static function getInfoForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Research Group Name')
                ->required()
                ->maxLength(400)
                ->placeholder('Computational Chemistry Research Group')
                ->prefixIcon('heroicon-o-academic-cap')
                ->helperText('Full name of the research group')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Enter the complete name of your research group.'),

            Forms\Components\Textarea::make('description')
                ->label('Group Description')
                ->maxLength(1000)
                ->placeholder('Brief description of the research group and its focus areas...')
                ->rows(3)
                ->helperText('Optional brief description of the research group')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Provide a short overview of your research group.'),

            Forms\Components\TextInput::make('faculty')
                ->label('Faculty')
                ->maxLength(255)
                ->placeholder('Faculty of Chemistry and Earth Sciences')
                ->prefixIcon('heroicon-o-building-library')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Name of the faculty or school.'),

            Forms\Components\TextInput::make('institute')
                ->label('Institute')
                ->maxLength(255)
                ->placeholder('Institute for Inorganic and Analytical Chemistry')
                ->prefixIcon('heroicon-o-building-office')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Name of the institute or department.'),

            Forms\Components\ToggleButtons::make('uses_eln')
                ->label('Does your team use an Electronic Lab Notebook (ELN)?')
                ->options([
                    true => 'Yes',
                    false => 'No'
                ])
                ->inline()
                ->boolean()
                ->default(false)
                ->live()
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Select if your research team uses any ELN system.'),

            Forms\Components\Select::make('eln_system')
                ->label('Which ELN System?')
                ->options([
                    'chemotion' => 'Chemotion ELN',
                    'elabftw' => 'eLabFTW',
                    'rspace' => 'RSpace',
                    'benchling' => 'Benchling',
                    'labarchives' => 'LabArchives',
                    'labguru' => 'LabGuru',
                    'sciformation' => 'SciFormation',
                    'other' => 'Other'
                ])
                ->prefixIcon('heroicon-o-wrench-screwdriver')
                ->visible(fn (Forms\Get $get) => $get('uses_eln') === true)
                ->live()
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Select your primary ELN system.'),

            Forms\Components\TextInput::make('eln_other')
                ->label('Other ELN System')
                ->maxLength(255)
                ->placeholder('Name of your ELN system')
                ->prefixIcon('heroicon-o-wrench-screwdriver')
                ->visible(fn (Forms\Get $get) => $get('uses_eln') === true && $get('eln_system') === 'other')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Please specify the name of your ELN system.'),
        ];
    }

    public static function getGroupLeaderForm(): array
    {
        return [
            Forms\Components\Section::make('Group Info')
                ->schema([
                    Forms\Components\TextInput::make('leader_name')
                        ->label('Principal Investigator')
                        ->maxLength(255)
                        ->placeholder('Prof. Dr. Christoph Steinbeck')
                        ->prefixIcon('heroicon-o-user')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Full name and title of the group leader.'),

                    Forms\Components\TextInput::make('orcid')
                        ->label('ORCID')
                        ->maxLength(50)
                        ->placeholder('0000-0001-6966-0814')
                        ->prefixIcon('heroicon-o-identification')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'ORCID identifier for the group leader.'),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(100)
                        ->placeholder('christoph.steinbeck@uni-jena.de')
                        ->prefixIcon('heroicon-o-envelope')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Primary contact email for the group leader.'),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('+49-3641-948171')
                        ->prefixIcon('heroicon-o-phone')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Direct phone number for the group leader.'),

                    Forms\Components\Textarea::make('office_address')
                        ->label('Office Address')
                        ->maxLength(500)
                        ->placeholder('Lessingstraße 8, Room 226, 07743 Jena, Germany')
                        ->rows(2)
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Physical office address.'),

                    Forms\Components\TextInput::make('website')
                        ->label('Website')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://cheminf.uni-jena.de')
                        ->prefixIcon('heroicon-o-globe-alt')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Personal or group website.'),
                ])
                ->columns(2),
        ];
    }

    public static function getResearchForm(): array
    {
        return [
            Forms\Components\Section::make('Research Focus')
                ->schema([
                    Forms\Components\TextInput::make('research_keywords')
                        ->label('Research Keywords')
                        ->maxLength(500)
                        ->placeholder('Cheminformatics, Computational Metabolomics, Natural Products, NMR Structure Elucidation')
                        ->prefixIcon('heroicon-o-hashtag')
                        ->helperText('Comma-separated keywords describing research areas')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Key research areas and methodologies.'),

                    Forms\Components\Textarea::make('research_description')
                        ->label('Research Description')
                        ->maxLength(2000)
                        ->placeholder('The research group specializes in computational natural products research...')
                        ->rows(4)
                        ->helperText('Detailed description of research focus and activities')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Comprehensive overview of research activities and goals.'),

                    Forms\Components\Textarea::make('funding_sources')
                        ->label('Funding Sources')
                        ->maxLength(1000)
                        ->placeholder('Deutsche Forschungsgemeinschaft (DFG), European Union (Horizon 2020), NFDI')
                        ->rows(2)
                        ->helperText('Primary funding organizations and grants')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Main sources of research funding.'),

                    Forms\Components\Select::make('preferred_language')
                        ->label('Preferred Communication Language')
                        ->options([
                            'english' => 'English',
                            'german' => 'German',
                            'english_german' => 'English and German',
                            'other' => 'Other'
                        ])
                        ->default('english')
                        ->prefixIcon('heroicon-o-language')
                        ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Primary language for group communications.'),
                ])
                ->columns(1),
        ];
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(MediaLibraryItem::class, 'logo_id', 'id');
    }

    public function getFilamentAvatarUrl(): string
    {
        return $this->owner->profile_photo_url;
    }

    public function getTenantId(): ?string
    {
        return auth()->user()?->company_id;
    }
}
