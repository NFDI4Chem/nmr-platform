<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpectrumTypeResource\Pages;
use App\Models\SpectrumType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SpectrumTypeResource extends Resource
{
    protected static ?string $model = SpectrumType::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Spectrum Types';

    protected static ?string $modelLabel = 'Spectrum Type';

    protected static ?string $pluralModelLabel = 'Spectrum Types';

    protected static ?string $navigationGroup = 'Platform Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Spectrum Type Information')
                    ->description('Define the spectrum type and its properties')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Spectrum Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., 1H NMR, 13C NMR, COSY, HSQC')
                            ->prefixIcon('heroicon-o-chart-bar')
                            ->helperText('Common name or abbreviation for the spectrum type'),

                        Forms\Components\TextInput::make('neuclei')
                            ->label('Nuclei')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., 1H, 13C, 31P, 19F')
                            ->prefixIcon('heroicon-o-cube')
                            ->helperText('Nuclei observed in this spectrum type'),

                        Forms\Components\Select::make('dimentionality')
                            ->label('Dimensionality')
                            ->options([
                                '1D' => '1D Spectrum',
                                '2D' => '2D Spectrum',
                            ])
                            ->required()
                            ->default('1D')
                            ->prefixIcon('heroicon-o-cube-transparent')
                            ->helperText('Spectrum dimensionality'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Additional information about this spectrum type, typical applications, etc.')
                            ->helperText('Optional detailed description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Spectrum Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Spectrum name copied!')
                    ->icon('heroicon-o-chart-bar')
                    ->description(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('neuclei')
                    ->label('Nuclei')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-cube'),

                Tables\Columns\TextColumn::make('dimentionality')
                    ->label('Dimension')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1D' => 'success',
                        '2D' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        '1D' => 'heroicon-o-chart-bar',
                        '2D' => 'heroicon-o-chart-bar-square',
                        default => 'heroicon-o-chart-bar',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('devices_count')
                    ->label('Devices')
                    ->counts('devices')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('samples_count')
                    ->label('Samples')
                    ->counts('samples')
                    ->badge()
                    ->color('gray')
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
                Tables\Filters\SelectFilter::make('dimentionality')
                    ->label('Dimensionality')
                    ->options([
                        '1D' => '1D Spectra',
                        '2D' => '2D Spectra',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('neuclei')
                    ->label('Nuclei Type')
                    ->options(function () {
                        return SpectrumType::query()
                            ->distinct()
                            ->pluck('neuclei', 'neuclei')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('dimentionality')
                    ->label('Dimensionality')
                    ->getTitleFromRecordUsing(fn ($record) => match ($record->dimentionality) {
                        '1D' => '1D Spectra',
                        '2D' => '2D Spectra',
                        default => 'Unknown',
                    })
                    ->collapsible(),

                Tables\Grouping\Group::make('neuclei')
                    ->label('Nuclei')
                    ->collapsible(),
            ])
            ->defaultGroup('dimentionality')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->modalHeading('Spectrum Type Details'),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this spectrum type? This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected spectrum types? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
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
            'index' => Pages\ListSpectrumTypes::route('/'),
            'create' => Pages\CreateSpectrumType::route('/create'),
            'edit' => Pages\EditSpectrumType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'neuclei', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nuclei' => $record->neuclei,
            'Dimension' => $record->dimentionality,
        ];
    }
}
