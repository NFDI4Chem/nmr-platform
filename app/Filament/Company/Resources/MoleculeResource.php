<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\MoleculeResource\Pages;
use App\Models\Molecule;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MoleculeResource extends Resource
{
    protected static ?string $model = Molecule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Molecules';

    protected static ?string $navigationGroup = 'Laboratory';

    protected static ?int $navigationSort = 2;

    // Molecules don't directly belong to a company, they're accessed through samples
    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        // Get molecules that are associated with samples belonging to this company
        return parent::getEloquentQuery()
            ->whereHas('samples', function (Builder $query) use ($tenant) {
                $query->where('company_id', $tenant?->getKey());
            })
            ->withCount(['samples' => function (Builder $query) use ($tenant) {
                $query->where('company_id', $tenant?->getKey());
            }]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Compound Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('identifier')
                            ->label('Identifier')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('iupac_name')
                            ->label('IUPAC Name')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Chemical Structure')
                    ->schema([
                        Forms\Components\TextInput::make('canonical_smiles')
                            ->label('Canonical SMILES')
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('molecular_formula')
                            ->label('Molecular Formula')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('structure')
                    ->label('Structure')
                    ->state(function ($record) {
                        if (empty($record->canonical_smiles)) {
                            return null;
                        }

                        return env('CM_PUBLIC_API', 'https://api.cheminf.studio/latest/')
                            .'depict/2D?smiles='.urlencode($record->canonical_smiles)
                            .'&height=150&width=150&CIP=true&toolkit=cdk';
                    })
                    ->width(100)
                    ->height(100)
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Compound Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->identifier)
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name),

                Tables\Columns\TextColumn::make('molecular_formula')
                    ->label('Formula')
                    ->searchable()
                    ->fontFamily('mono')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('canonical_smiles')
                    ->label('SMILES')
                    ->searchable()
                    ->fontFamily('mono')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->canonical_smiles)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('samples_count')
                    ->label('Samples')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->tooltip('Number of samples using this molecule'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_structure')
                    ->label('Has Structure')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('canonical_smiles')),

                Tables\Filters\Filter::make('has_formula')
                    ->label('Has Formula')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('molecular_formula')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Molecule'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for viewing
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Company\Resources\MoleculeResource\RelationManagers\SamplesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMolecules::route('/'),
            'view' => Pages\ViewMolecule::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return null;
        }

        return static::getModel()::whereHas('samples', function (Builder $query) use ($tenant) {
            $query->where('company_id', $tenant->getKey());
        })->count();
    }

    public static function canCreate(): bool
    {
        return false; // Molecules are created through samples
    }
}

