<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;
use Filament\Forms\Get;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryFolder;
use Auth;
use Filament\Facades\Filament;
use App\Filament\Traits\MutatesSampleFormData;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\ModelStates\HasStates;
use App\States\Sample\SampleState;
use App\States\Sample\ToRejected;
use Filament\Tables;
use Maartenpaauw\Filament\ModelStates\StateSelectColumn;
use Maartenpaauw\Filament\ModelStates\StateSelect;

class Sample extends Model
{
    use HasFactory;
    use MutatesSampleFormData;
    use HasStates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'company_id',
        'user_id',
        'identifier',
        'solvent_id',
        'molecule_id',
        'spectrum_type',
        'other_nuclei',
        'automation',
        'featured_molfile_id',
        'instructions',
        'featured_image_id',
        'priority',
        'operator_id',
        'status',
        'finished_file_id',
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
        // 'featured_image_id' => 'array',
        // 'featured_molfile_id' => 'array',
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
        return $this->belongsTo(MediaLibraryItem::class, 'featured_image_id');
    }

    public static function getForm(): array
    {
        return [
            Section::make('Tracking info')
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Sample ID')
                        ->prefix(function (string $operation) {
                            if ($operation == 'create') {
                                return 'NMR-' . date('Ym') . '-ID-';
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
                    // Forms\Components\TextInput::make('identifier')
                    //     ->maxLength(255),

                    Forms\Components\select::make('priority')
                        // ->label('Sample Priority')
                        ->options(getPriority())
                        ->default('LOW'),
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
                    Fieldset::make('Attach a mol file')
                        ->schema([
                            MediaPicker::make('featured_molfile_id')
                                ->label('')
                                ->folder(function (MediaLibraryFolder $folder) {
                                    if (Filament::getTenant()) {
                                        $group_folder_slug = Filament::getTenant()->slug . '-' . Filament::getTenant()->id;
                                        $found_folder_id = MediaLibraryFolder::where([
                                            ['name', $group_folder_slug],
                                            ['company_id', Filament::getTenant()->id],
                                        ])->get()[0]->id;
                                        return $folder->find($found_folder_id);
                                    };
                                }),
                        ]),
                ])
                ->columns(2),
            Section::make('Sample info')
                ->schema([
                    Forms\Components\Select::make('solvent_id')
                        ->relationship('solvent', 'name'),
                    Forms\Components\Select::make('molecule_id')
                        ->relationship('molecule', 'name'),
                    Forms\Components\Textarea::make('instructions')
                        ->label('Special care for sample')
                        ->columnSpanFull(),
                    Fieldset::make('Attach a file for the operator')
                        ->schema([
                            MediaPicker::make('featured_image_id')
                                ->label('')
                                ->folder(function (MediaLibraryFolder $folder) {
                                    if (Filament::getTenant()) {
                                        $group_folder_slug = Filament::getTenant()->slug . '-' . Filament::getTenant()->id;
                                        $found_folder_id = MediaLibraryFolder::where([
                                            ['name', $group_folder_slug],
                                            ['company_id', Filament::getTenant()->id],
                                        ])->get()[0]->id;
                                        return $folder->find($found_folder_id);
                                    }
                                }),
                        ]),
                    StateSelect::make('status')
                        ->label('Sample Status')
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
            Tables\Columns\TextColumn::make('identifier')
                ->searchable(),
            Tables\Columns\TextColumn::make('solvent.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('molecule.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('spectrum_type')
                ->searchable(),
            // Tables\Columns\TextColumn::make('featured_image_id')
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
