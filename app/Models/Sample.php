<?php

namespace App\Models;

use App\Filament\Traits\MutatesSampleFormData;
use App\States\Sample\SampleState;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Tables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Maartenpaauw\Filament\ModelStates\StateSelect;
use Maartenpaauw\Filament\ModelStates\StateSelectColumn;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Spatie\ModelStates\HasStates;

class Sample extends Model
{
    use HasFactory;
    use HasStates;
    use MutatesSampleFormData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'company_id',
        'user_id',
        'reference',
        'ticker_id',
        'personal_key',
        'solvent_id',
        'molecule_id',
        'spectrum_type',
        'other_nuclei',
        'automation',
        'molfile_id',
        'instructions',
        'additional_infofile_id',
        'priority',
        'operator_id',
        'status',
        'rawdata_file_id',
        'comments',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'device_id' => 'integer',
        'company_id' => 'integer',
        'solvent_id' => 'integer',
        'molecule_id' => 'integer',
        'operator_id' => 'integer',
        'status' => SampleState::class,
        // 'additional_infofile_id' => 'array',
        // 'molfile_id' => 'array',
    ];

    protected $group_folder_slug = '';

    public function spectrumTypesOfDevices(): HasManyThrough
    {
        return $this->hasManyThrough(SpectrumType::class, Device::class);
    }

    public function spectrumTypes(): BelongsToMany
    {
        return $this->belongsToMany(SpectrumType::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function solvent(): BelongsTo
    {
        return $this->belongsTo(Solvent::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function molecule(): BelongsTo
    {
        return $this->belongsTo(Molecule::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaLibraryItem::class, 'additional_infofile_id');
    }

    public static function getForm(): array
    {
        return [
            Section::make('Tracking info')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('Sample ID')
                        ->prefix(function (string $operation) {
                            if ($operation == 'create') {
                                return 'NMR-'.date('Ym').'-ID-';
                            } else {
                                return '';
                            }
                        })
                        ->placeholder('Enter a keyword for your personal use')
                        ->readOnly(function (string $operation) {
                            if ($operation == 'edit') {
                                return true;
                            } else {
                                return false;
                            }
                        }),
                ])
                ->columns(2),
            Section::make('Configuration')
                ->schema([

                    // Forms\Components\Select::make('device_id')
                    //     ->relationship('device', 'name')
                    //     ->live(),
                    // Forms\Components\Select::make('company_id')
                    //     ->relationship('company', 'name')
                    //     ->label('Group Name')
                    //     ->required(),
                    // Forms\Components\TextInput::make('reference')
                    //     ->maxLength(255),

                    Forms\Components\Select::make('spectrum_type')
                        ->relationship('spectrumTypes', 'name')
                        ->multiple()
                        ->options(function (Get $get) {
                            return SpectrumType::all()->pluck('name', 'id');
                        }),
                    Forms\Components\TextInput::make('other_nuclei')
                        ->label('Other Nuclei (please specify)')
                        ->maxLength(255),
                    Forms\Components\Checkbox::make('automation')
                        ->helperText('Do you want to process this sample automatically?'),
                ])
                ->columns(2),
            Section::make('Structure info')
                ->schema([
                    Forms\Components\Select::make('molecule_id')
                        ->relationship('molecule', 'name'),
                    Fieldset::make('Attach a mol file')
                        ->schema([
                            MediaPicker::make('molfile_id')
                                ->label('')
                                ->folder(function (MediaLibraryFolder $folder) {
                                    if (Filament::getTenant()) {
                                        $group_folder_slug = Filament::getTenant()->slug.'-'.Filament::getTenant()->id;
                                        $found_folder_id = MediaLibraryFolder::where([
                                            ['name', $group_folder_slug],
                                            ['company_id', Filament::getTenant()->id],
                                        ])->get()[0]->id;

                                        return $folder->find($found_folder_id);
                                    }
                                })
                                ->buttonLabel('Choose mol file'),
                        ]),
                ])
                ->columns(2),
            Section::make('Sample info')
                ->schema([
                    Forms\Components\Select::make('solvent_id')
                        ->relationship('solvent', 'name'),

                    Forms\Components\Textarea::make('instructions')
                        ->label('Special care for sample')
                        ->columnSpanFull(),
                    Fieldset::make('Attach a file for the operator')
                        ->schema([
                            MediaPicker::make('additional_infofile_id')
                                ->label('')
                                ->folder(function (MediaLibraryFolder $folder) {
                                    if (Filament::getTenant()) {
                                        $group_folder_slug = Filament::getTenant()->slug.'-'.Filament::getTenant()->id;
                                        $found_folder_id = MediaLibraryFolder::where([
                                            ['name', $group_folder_slug],
                                            ['company_id', Filament::getTenant()->id],
                                        ])->get()[0]->id;

                                        return $folder->find($found_folder_id);
                                    }
                                })
                                ->buttonLabel('Choose file'),
                        ]),
                    Forms\Components\select::make('priority')
                        // ->label('Sample Priority')
                        ->options(getPriority())
                        ->default('LOW'),
                    StateSelect::make('status')
                        ->label('Sample Status')
                        ->hidden()
                        ->disabled(function (string $operation) {
                            if ($operation == 'edit') {
                                return false;
                            }

                            return true;
                        }),

                ])
                ->columns(2),

            // Forms\Components\Select::make('operator_id')
            //     ->relationship('user', 'name'),
        ];
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('device.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('company.name')
                ->numeric()
                ->sortable()
                ->hidden(function () {
                    return Filament::getTenant() ? true : false;
                }),
            Tables\Columns\TextColumn::make('reference')
                ->searchable(),
            Tables\Columns\TextColumn::make('solvent.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('molecule.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('spectrum_type')
                ->searchable(),
            // Tables\Columns\TextColumn::make('additional_infofile_id')
            //     ->searchable(),
            Tables\Columns\TextColumn::make('priority')
                ->searchable(),
            Tables\Columns\TextColumn::make('operator.name')
                ->numeric()
                ->sortable(),
            StateSelectColumn::make('status'),
            // Tables\Columns\TextColumn::make('reject')
            //     ->badge()
            //     ->default('REJECT')
            //     ->action(function (Sample $record): void {
            //         $this->dispatch('open-post-edit-modal', post: $record->getKey());
            //     }),

            // Tables\Columns\TextColumn::make('created_at')
            //     ->dateTime()
            //     ->sortable()
            //     ->toggleable(isToggledHiddenByDefault: true),
            // Tables\Columns\TextColumn::make('updated_at')
            //     ->dateTime()
            //     ->sortable()
            //     ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
