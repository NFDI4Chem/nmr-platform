<?php

namespace App\Models;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Tables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Sample extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

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
                    Forms\Components\CheckboxList::make('spectrum_type')
                        ->relationship('spectrumTypes', 'name')
                        ->label('Spectrum Types')
                        ->options(function (Get $get) {
                            return SpectrumType::all()->pluck('name', 'id');
                        })
                        ->columns(3)
                        ->gridDirection('row')
                        ->columnSpanFull(),
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
                    Forms\Components\FileUpload::make('molfile')
                        ->label('Mol file')
                        ->acceptedFileTypes(['chemical/x-mdl-molfile'])
                        ->maxSize(1024),
                ])
                ->columns(2),
            Section::make('Sample info')
                ->schema([
                    Forms\Components\Radio::make('solvent_id')
                        ->label('Solvent')
                        ->required()
                        ->options(function () {
                            return Solvent::where('active', true)->pluck('name', 'id');
                        })
                        ->columns(3)
                        ->columnSpanFull(),
                    Forms\Components\Radio::make('priority')
                        ->label('Priority')
                        ->options([
                            'LOW' => 'Low',
                            'MEDIUM' => 'Medium',
                            'HIGH' => 'High',
                            'URGENT' => 'Urgent',
                        ])
                        ->default('LOW')
                        ->inline()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('instructions')
                        ->label('Special care for sample')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('additional_infofile')
                        ->label('Info file')
                        ->acceptedFileTypes(['application/pdf', 'text/plain', 'image/*'])
                        ->maxSize(2048),
                    Forms\Components\TextInput::make('status')
                        ->label('Sample Status')
                        ->default('submitted')
                        ->hidden(),
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
            Tables\Columns\TextColumn::make('priority')
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'submitted' => 'info',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'completed' => 'success',
                    default => 'gray',
                }),
        ];
    }
}
