<?php

namespace App\Models;

use App\Filament\Traits\MutatesSampleFormData;
use App\Forms\Components\InfoDownload as InfoDownloadField;
use App\Forms\Components\MolDownload as MolDownloadField;
use App\States\Sample\SampleState;
use App\Tables\Columns\InfoDownload;
use App\Tables\Columns\MolDownload;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Get;
use Filament\Tables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Maartenpaauw\Filament\ModelStates\StateSelect;
use Maartenpaauw\Filament\ModelStates\StateSelectColumn;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\ModelStates\HasStates;

class Sample extends Model implements HasMedia
{
    use HasFactory;
    use HasStates;
    use InteractsWithMedia;
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
                    SpatieMediaLibraryFileUpload::make('molfile')
                        ->label('Mol file')
                        ->conversion('thumb')
                        ->collection('molfile')
                        ->hidden(function (string $operation) {
                            if ($operation == 'edit') {
                                return true;
                            }

                            return false;
                        }),
                    MolDownloadField::make('molfile')
                        ->label('Mol file')
                        ->hidden(function (string $operation) {
                            if ($operation == 'edit') {
                                return false;
                            }

                            return true;
                        }),
                ])
                ->columns(2),
            Section::make('Sample info')
                ->schema([
                    Forms\Components\Select::make('solvent_id')
                        ->relationship('solvent', 'name'),
                    Forms\Components\select::make('priority')
                        // ->label('Sample Priority')
                        ->options(getPriority())
                        ->default('LOW'),
                    Forms\Components\Textarea::make('instructions')
                        ->label('Special care for sample')
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('additional_infofile_id')
                        ->label('Info file')
                        ->conversion('thumb')
                        ->collection('infofile')
                        ->hidden(function (string $operation) {
                            if ($operation == 'edit') {
                                return true;
                            }

                            return false;
                        }),
                    InfoDownloadField::make('infofile')
                        ->label('Info file')
                        ->hidden(function (string $operation) {
                            if ($operation == 'edit') {
                                return false;
                            }

                            return true;
                        }),
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

        ];
    }

    public static function getTableColumns(): array
    {
        return [
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
            // Tables\Columns\TextColumn::make('molecule.name')
            //     ->numeric()
            //     ->sortable(),
            // Tables\Columns\TextColumn::make('spectrum_type')
            //     ->searchable(),
            Tables\Columns\TextColumn::make('priority')
                ->searchable(),
            MolDownload::make('molfile')
                ->label('Mol File')
                ->alignCenter(),
            InfoDownload::make('infofile')
                ->label('Info File')
                ->alignCenter(),
            // Tables\Columns\TextColumn::make('operator.name')
            //     ->numeric()
            //     ->sortable(),
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
