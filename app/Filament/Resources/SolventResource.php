<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolventResource\Pages;
use App\Models\Solvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SolventResource extends Resource
{
    protected static ?string $model = Solvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Solvents';

    protected static ?string $modelLabel = 'Solvent';

    protected static ?string $pluralModelLabel = 'Solvents';

    protected static ?string $navigationGroup = 'Platform Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Solvent Information')
                    ->description('Basic information about the NMR solvent')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Solvent Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., CDCl3, DMSO-d6, D2O')
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Common name or abbreviation for the NMR solvent'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Additional information about the solvent, usage notes, or special considerations...')
                            ->helperText('Optional description or usage notes'),

                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->helperText('Enable or disable this solvent for use in samples')
                            ->default(true),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Chemical Properties')
                    ->description('Chemical and physical properties of the solvent')
                    ->schema([
                        Forms\Components\TextInput::make('molecular_formula')
                            ->label('Molecular Formula')
                            ->maxLength(100)
                            ->placeholder('e.g., CDCl3, C2D6SO')
                            ->prefixIcon('heroicon-o-academic-cap')
                            ->helperText('Chemical formula of the solvent'),

                        Forms\Components\TextInput::make('molecular_weight')
                            ->label('Molecular Weight')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(9999.99)
                            ->suffix('g/mol')
                            ->placeholder('e.g., 119.38')
                            ->prefixIcon('heroicon-o-scale')
                            ->helperText('Molecular weight in g/mol'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Data')
                    ->description('Additional metadata and properties')
                    ->schema([
                        Forms\Components\KeyValue::make('meta_data')
                            ->label('Additional Properties')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Property')
                            ->helperText('Additional chemical properties, NMR parameters, or reference data')
                            ->columnSpanFull(),
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
                    ->label('Solvent Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Solvent name copied!')
                    ->icon('heroicon-o-beaker'),

                Tables\Columns\TextColumn::make('molecular_formula')
                    ->label('Formula')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Formula copied!')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('molecular_weight')
                    ->label('MW (g/mol)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->placeholder('No description'),

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
            ->groups([
                Tables\Grouping\Group::make('active')
                    ->label('Status')
                    ->getTitleFromRecordUsing(fn ($record) => $record->active ? 'Active Solvents' : 'Inactive Solvents')
                    ->collapsible(),

                Tables\Grouping\Group::make('molecular_weight')
                    ->label('Molecular Weight Range')
                    ->getTitleFromRecordUsing(function ($record) {
                        if (! $record->molecular_weight) {
                            return 'Unknown Weight';
                        }
                        $weight = $record->molecular_weight;
                        if ($weight < 50) {
                            return 'Light (< 50 g/mol)';
                        }
                        if ($weight < 100) {
                            return 'Medium (50-100 g/mol)';
                        }
                        if ($weight < 150) {
                            return 'Heavy (100-150 g/mol)';
                        }

                        return 'Very Heavy (> 150 g/mol)';
                    })
                    ->collapsible(),

                Tables\Grouping\Group::make('name')
                    ->label('Solvent Type')
                    ->getTitleFromRecordUsing(function ($record) {
                        $name = strtolower($record->name);
                        if (str_contains($name, 'chloroform') || str_contains($name, 'cdcl')) {
                            return 'Chlorinated Solvents';
                        }
                        if (str_contains($name, 'dmso')) {
                            return 'Sulfoxide Solvents';
                        }
                        if (str_contains($name, 'methanol') || str_contains($name, 'cd3od')) {
                            return 'Alcohol Solvents';
                        }
                        if (str_contains($name, 'water') || str_contains($name, 'd2o')) {
                            return 'Aqueous Solvents';
                        }
                        if (str_contains($name, 'acetone')) {
                            return 'Ketone Solvents';
                        }
                        if (str_contains($name, 'benzene')) {
                            return 'Aromatic Solvents';
                        }

                        return 'Other Solvents';
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('active')
            ->filters([
                Tables\Filters\Filter::make('has_molecular_weight')
                    ->label('Has Molecular Weight')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('molecular_weight')),

                Tables\Filters\Filter::make('has_formula')
                    ->label('Has Formula')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('molecular_formula')),

                Tables\Filters\Filter::make('active_only')
                    ->label('Active Only')
                    ->query(fn (Builder $query): Builder => $query->where('active', true)),

                Tables\Filters\Filter::make('inactive_only')
                    ->label('Inactive Only')
                    ->query(fn (Builder $query): Builder => $query->where('active', false)),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->active ? 'Disable' : 'Enable')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->active ? 'Disable Solvent' : 'Enable Solvent')
                    ->modalDescription(fn ($record) => $record->active
                        ? 'Are you sure you want to disable this solvent? It will no longer be available for sample submissions.'
                        : 'Are you sure you want to enable this solvent? It will become available for sample submissions.')
                    ->modalSubmitActionLabel(fn ($record) => $record->active ? 'Disable' : 'Enable')
                    ->action(function ($record) {
                        $wasActive = $record->active;
                        $record->update(['active' => ! $record->active]);

                        $status = $wasActive ? 'disabled' : 'enabled';

                        \Filament\Notifications\Notification::make()
                            ->title("Solvent {$status}")
                            ->body("The solvent '{$record->name}' has been {$status}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->modalHeading('Solvent Details'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this solvent? This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Enable Solvents')
                        ->modalDescription('Are you sure you want to enable the selected solvents? They will become available for sample submissions.')
                        ->modalSubmitActionLabel('Enable')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['active' => true]));

                            \Filament\Notifications\Notification::make()
                                ->title('Solvents enabled')
                                ->body("{$count} solvent(s) have been enabled.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Disable Solvents')
                        ->modalDescription('Are you sure you want to disable the selected solvents? They will no longer be available for sample submissions.')
                        ->modalSubmitActionLabel('Disable')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['active' => false]));

                            \Filament\Notifications\Notification::make()
                                ->title('Solvents disabled')
                                ->body("{$count} solvent(s) have been disabled.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected solvents? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->searchOnBlur();
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
            'index' => Pages\ListSolvents::route('/'),
            'create' => Pages\CreateSolvent::route('/create'),
            'edit' => Pages\EditSolvent::route('/{record}/edit'),
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
        return ['name', 'molecular_formula', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Formula' => $record->molecular_formula ?? 'No formula',
            'MW' => $record->molecular_weight ? $record->molecular_weight.' g/mol' : 'No MW',
        ];
    }
}
