<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Devices';

    protected static ?string $modelLabel = 'Device';

    protected static ?string $pluralModelLabel = 'Devices';

    protected static ?string $navigationGroup = 'Platform Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->description('Basic information about the NMR device')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Device Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Bruker Avance III 500')
                            ->prefixIcon('heroicon-o-cpu-chip')
                            ->helperText('Common name or identifier for the device'),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Manufacturer')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Bruker, Varian, JEOL')
                            ->prefixIcon('heroicon-o-building-office')
                            ->helperText('Device manufacturer or brand'),

                        Forms\Components\TextInput::make('model_no')
                            ->label('Model Number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Avance III 500')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Manufacturer model number or series'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'ACTIVE' => 'Active',
                                'INACTIVE' => 'Inactive',
                            ])
                            ->default('ACTIVE')
                            ->required()
                            ->prefixIcon('heroicon-o-signal')
                            ->helperText('Current operational status of the device'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Spectrum Types')
                    ->description('Select the spectrum types this device can perform')
                    ->schema([
                        Forms\Components\Select::make('spectrumTypes')
                            ->label('Supported Spectrum Types')
                            ->relationship('spectrumTypes', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('neuclei')
                                    ->label('Nuclei')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('dimentionality')
                                    ->label('Dimensionality')
                                    ->options([
                                        '1D' => '1D',
                                        '2D' => '2D',
                                    ])
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(1000)
                                    ->rows(3),
                            ])
                            ->helperText('Select all spectrum types that can be performed on this device'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Device Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Device name copied!')
                    ->icon('heroicon-o-cpu-chip')
                    ->description(fn ($record) => $record->model_no),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Manufacturer')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('model_no')
                    ->label('Model Number')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'heroicon-o-check-circle',
                        'INACTIVE' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('spectrumTypes.name')
                    ->label('Spectrum Types')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('samples_count')
                    ->label('Samples')
                    ->counts('samples')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive',
                    ])
                    ->default('ACTIVE'),

                Tables\Filters\SelectFilter::make('manufacturer')
                    ->label('Manufacturer')
                    ->options(function () {
                        return Device::query()
                            ->distinct()
                            ->pluck('manufacturer', 'manufacturer')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('spectrumTypes')
                    ->label('Spectrum Type')
                    ->relationship('spectrumTypes', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label('')
                    ->tooltip(fn ($record) => $record->status === 'ACTIVE' ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->status === 'ACTIVE' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->status === 'ACTIVE' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->status === 'ACTIVE' ? 'Deactivate Device' : 'Activate Device')
                    ->modalDescription(fn ($record) => $record->status === 'ACTIVE'
                        ? 'Are you sure you want to deactivate this device? It will no longer be available for sample submissions.'
                        : 'Are you sure you want to activate this device? It will become available for sample submissions.')
                    ->modalSubmitActionLabel(fn ($record) => $record->status === 'ACTIVE' ? 'Deactivate' : 'Activate')
                    ->action(function ($record) {
                        $newStatus = $record->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
                        $record->update(['status' => $newStatus]);

                        \Filament\Notifications\Notification::make()
                            ->title("Device {$newStatus}")
                            ->body("The device '{$record->name}' has been {$newStatus}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->modalHeading('Device Details'),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this device? This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Devices')
                        ->modalDescription('Are you sure you want to activate the selected devices?')
                        ->modalSubmitActionLabel('Activate')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['status' => 'ACTIVE']));

                            \Filament\Notifications\Notification::make()
                                ->title('Devices activated')
                                ->body("{$count} device(s) have been activated.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Devices')
                        ->modalDescription('Are you sure you want to deactivate the selected devices?')
                        ->modalSubmitActionLabel('Deactivate')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['status' => 'INACTIVE']));

                            \Filament\Notifications\Notification::make()
                                ->title('Devices deactivated')
                                ->body("{$count} device(s) have been deactivated.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected devices? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SpectrumTypesRelationManager::class,
            RelationManagers\SamplesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'ACTIVE')->count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'manufacturer', 'model_no'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Manufacturer' => $record->manufacturer,
            'Model' => $record->model_no,
            'Status' => $record->status,
        ];
    }
}
