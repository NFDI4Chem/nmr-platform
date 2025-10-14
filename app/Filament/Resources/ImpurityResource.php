<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpurityResource\Pages;
use App\Models\Impurity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ImpurityResource extends Resource
{
    protected static ?string $model = Impurity::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Impurities';

    protected static ?string $modelLabel = 'Impurity';

    protected static ?string $pluralModelLabel = 'Impurities';

    protected static ?string $navigationGroup = 'Platform Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Impurity Information')
                    ->description('Basic information about the NMR impurity')
                    ->schema([
                        Forms\Components\TagsInput::make('names')
                            ->label('Compound Names')
                            ->required()
                            ->placeholder('Enter compound names...')
                            ->helperText('Alternative names for this compound')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('smiles')
                            ->label('SMILES Notation')
                            ->maxLength(500)
                            ->placeholder('e.g., CC(=O)O')
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Simplified molecular-input line-entry system notation'),

                        Forms\Components\Select::make('nucleus')
                            ->label('Nucleus Type')
                            ->required()
                            ->options([
                                '1H' => 'Proton (¹H)',
                                '13C' => 'Carbon (¹³C)',
                            ])
                            ->default('1H')
                            ->prefixIcon('heroicon-o-atom')
                            ->helperText('Type of nucleus observed'),

                        Forms\Components\TextInput::make('solvent')
                            ->label('Solvent')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('e.g., THF-d₈, CDCl₃')
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Solvent used in NMR spectroscopy'),

                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->helperText('Enable or disable this impurity for use in analysis')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('NMR Data')
                    ->description('NMR spectroscopic data and ranges')
                    ->schema([
                        Forms\Components\KeyValue::make('ranges')
                            ->label('NMR Ranges')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Range')
                            ->helperText('NMR spectroscopic ranges and signals')
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
                Tables\Columns\ImageColumn::make('structure')
                    ->label('Structure')
                    ->state(function ($record) {
                        if (empty($record->smiles)) {
                            return null;
                        }
                        return env('CM_PUBLIC_API', 'https://api.cheminf.studio/latest/')
                            .'depict/2D?smiles='.urlencode($record->smiles)
                            .'&height=300&width=300&CIP=true&toolkit=cdk';
                    })
                    ->width(150)
                    ->height(150)
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('names')
                    ->label('Compound Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->smiles ? 'SMILES: ' . $record->smiles : null)
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(50)
                    ->tooltip(fn ($record) => is_array($record->names) ? implode(', ', $record->names) : $record->names)
                    ->icon('heroicon-o-beaker')
                    ->copyable()
                    ->copyMessage('Compound name copied!')
                    ->copyableState(fn ($record) => $record->smiles ?: (is_array($record->names) ? implode(', ', $record->names) : $record->names)),

                Tables\Columns\TextColumn::make('nucleus')
                    ->label('Nucleus')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1H' => 'success',
                        '13C' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1H' => 'Proton (¹H)',
                        '13C' => 'Carbon (¹³C)',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('solvent')
                    ->label('Solvent')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean()
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
                Tables\Filters\SelectFilter::make('nucleus')
                    ->label('Nucleus Type')
                    ->options([
                        '1H' => 'Proton (¹H)',
                        '13C' => 'Carbon (¹³C)',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('solvent')
                    ->label('Solvent')
                    ->options(function () {
                        return Impurity::query()
                            ->distinct()
                            ->pluck('solvent', 'solvent')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All impurities')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->default(true),
            ])
            ->groups([
                Tables\Grouping\Group::make('nucleus')
                    ->label('Nucleus Type')
                    ->getTitleFromRecordUsing(fn ($record) => match ($record->nucleus) {
                        '1H' => 'Proton (¹H) NMR',
                        '13C' => 'Carbon (¹³C) NMR',
                        default => $record->nucleus,
                    })
                    ->collapsible(),

                Tables\Grouping\Group::make('solvent')
                    ->label('Solvent')
                    ->collapsible(),

                Tables\Grouping\Group::make('active')
                    ->label('Status')
                    ->getTitleFromRecordUsing(fn ($record) => $record->active ? 'Active Impurities' : 'Inactive Impurities')
                    ->collapsible(),
            ])
            ->defaultGroup('nucleus')
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label('')
                    ->tooltip(fn ($record) => $record->active ? 'Disable' : 'Enable')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->active ? 'Disable Impurity' : 'Enable Impurity')
                    ->modalDescription(fn ($record) => $record->active
                        ? 'Are you sure you want to disable this impurity? It will no longer be available for NMR analysis.'
                        : 'Are you sure you want to enable this impurity? It will become available for NMR analysis.')
                    ->modalSubmitActionLabel(fn ($record) => $record->active ? 'Disable' : 'Enable')
                    ->action(function ($record) {
                        $wasActive = $record->active;
                        $record->update(['active' => ! $record->active]);

                        $status = $wasActive ? 'disabled' : 'enabled';

                        \Filament\Notifications\Notification::make()
                            ->title("Impurity {$status}")
                            ->body("The impurity has been {$status}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->modalHeading('Impurity Details'),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this impurity? This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Enable Impurities')
                        ->modalDescription('Are you sure you want to enable the selected impurities? They will become available for NMR analysis.')
                        ->modalSubmitActionLabel('Enable')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['active' => true]));

                            \Filament\Notifications\Notification::make()
                                ->title('Impurities enabled')
                                ->body("{$count} impurity/impurities have been enabled.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Disable Impurities')
                        ->modalDescription('Are you sure you want to disable the selected impurities? They will no longer be available for NMR analysis.')
                        ->modalSubmitActionLabel('Disable')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['active' => false]));

                            \Filament\Notifications\Notification::make()
                                ->title('Impurities disabled')
                                ->body("{$count} impurity/impurities have been disabled.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected impurities? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('id')
            ->searchOnBlur()
            ->recordAction(null)
            ->recordClasses(fn () => 'hover:!bg-white dark:hover:!bg-gray-900');
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
            'index' => Pages\ListImpurities::route('/'),
            'create' => Pages\CreateImpurity::route('/create'),
            'edit' => Pages\EditImpurity::route('/{record}/edit'),
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
        return ['names', 'smiles', 'solvent', 'nucleus'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $names = is_array($record->names) ? implode(', ', $record->names) : $record->names;
        
        return [
            'Names' => $names,
            'Nucleus' => $record->nucleus,
            'Solvent' => $record->solvent,
        ];
    }
}

