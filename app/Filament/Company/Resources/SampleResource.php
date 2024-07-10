<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\SampleResource\Pages;
use App\Filament\Company\Resources\SampleResource\RelationManagers;
use App\Models\Sample;
use App\Models\SpectrumType;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;
use Filament\Forms\Get;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-m-cube-transparent';

    protected static ?string $navigationGroup = 'Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tracking info')
                    ->schema([
                        Forms\Components\TextInput::make('reference_id')
                            ->label('Sample ID')
                            ->prefix('NMR-' . date("Ym") . '-ID-')
                            ->placeholder('Enter a keyword for personal use'),
                    ])
                    ->columns(2),
                Section::make('Configuration')
                    ->schema([

                        Forms\Components\Select::make('device_id')
                            ->relationship('device', 'name')
                            ->live(),
                        // Forms\Components\Select::make('company_id')
                        //     ->relationship('company', 'name')
                        //     ->label('Group Name')
                        //     ->required(),
                        // Forms\Components\TextInput::make('identifier')
                        //     ->maxLength(255),

                        Forms\Components\TextInput::make('priority')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('spectrum_type')
                            ->multiple()
                            ->options(function (Get $get) {
                                return Device::find($get('device_id'))?->spectrumTypes()->pluck('name', 'id');
                            }),
                        Forms\Components\TextInput::make('other_nuclei')
                            ->label('Other Nuclei (please specify)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('is_automation')
                            ->label('Automation')
                            ->helperText('Do you want to process this sample automatically?'),
                    ])
                    ->columns(2),
                Section::make('Structure info')
                    ->schema([
                        Fieldset::make('Attach a mol file')
                        ->schema([
                            MediaPicker::make('featured_molecule_id')
                                ->label(''),
                        ])
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
                                    ->label(''),
                            ])
                    ])
                    ->columns(2),







                // Forms\Components\Select::make('operator_id')
                //     ->relationship('user', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSamples::route('/'),
            'create' => Pages\CreateSample::route('/create'),
            'edit' => Pages\EditSample::route('/{record}/edit'),
        ];
    }
}
