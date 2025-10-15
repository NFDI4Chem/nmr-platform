<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoleculeResource\Pages;
use App\Models\Molecule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MoleculeResource extends Resource
{
    protected static ?string $model = Molecule::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Molecules';

    protected static ?string $modelLabel = 'Molecule';

    protected static ?string $pluralModelLabel = 'Molecules';

    protected static ?string $navigationGroup = 'Platform Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->description('Basic molecular identification information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Compound Name')
                            ->maxLength(255)
                            ->placeholder('Common name of the compound')
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Primary name of the molecule'),

                        Forms\Components\TextInput::make('identifier')
                            ->label('Identifier')
                            ->maxLength(255)
                            ->placeholder('Unique identifier')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Unique identifier for this molecule'),

                        Forms\Components\TextInput::make('iupac_name')
                            ->label('IUPAC Name')
                            ->maxLength(1000)
                            ->placeholder('Systematic IUPAC name')
                            ->prefixIcon('heroicon-o-academic-cap')
                            ->helperText('IUPAC systematic name')
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('synonyms')
                            ->label('Synonyms')
                            ->placeholder('Enter synonyms')
                            ->helperText('Alternative names for this compound')
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('cas')
                            ->label('CAS Numbers')
                            ->placeholder('Enter CAS registry numbers')
                            ->helperText('CAS registry numbers')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Chemical Structure')
                    ->description('Chemical structure representations')
                    ->schema([
                        Forms\Components\TextInput::make('canonical_smiles')
                            ->label('Canonical SMILES')
                            ->maxLength(5000)
                            ->placeholder('e.g., CC(=O)O')
                            ->prefixIcon('heroicon-o-code-bracket')
                            ->helperText('Canonical SMILES notation')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('sugar_free_smiles')
                            ->label('Sugar-Free SMILES')
                            ->maxLength(5000)
                            ->placeholder('SMILES without sugar moieties')
                            ->prefixIcon('heroicon-o-code-bracket')
                            ->helperText('SMILES notation without sugar groups')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('standard_inchi')
                            ->label('Standard InChI')
                            ->maxLength(5000)
                            ->rows(2)
                            ->placeholder('InChI=1S/...')
                            ->helperText('Standard InChI representation')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('standard_inchi_key')
                            ->label('InChI Key')
                            ->maxLength(255)
                            ->placeholder('BQJCRHHNABKAKU-KBQPJGBKSA-N')
                            ->prefixIcon('heroicon-o-key')
                            ->helperText('InChI key for fast lookup')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('murcko_framework')
                            ->label('Murcko Framework')
                            ->maxLength(5000)
                            ->placeholder('Molecular framework')
                            ->prefixIcon('heroicon-o-square-3-stack-3d')
                            ->helperText('Bemis-Murcko scaffold')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Metadata & Annotations')
                    ->description('Annotation levels and metadata')
                    ->schema([
                        Forms\Components\Select::make('annotation_level')
                            ->label('Annotation Level')
                            ->options([
                                0 => 'Level 0 - Unknown',
                                1 => 'Level 1 - Partial',
                                2 => 'Level 2 - Good',
                                3 => 'Level 3 - Excellent',
                                4 => 'Level 4 - Complete',
                            ])
                            ->default(0)
                            ->prefixIcon('heroicon-o-star'),

                        Forms\Components\Select::make('name_trust_level')
                            ->label('Name Trust Level')
                            ->options([
                                0 => 'Level 0 - Unknown',
                                1 => 'Level 1 - Low',
                                2 => 'Level 2 - Medium',
                                3 => 'Level 3 - High',
                                4 => 'Level 4 - Verified',
                            ])
                            ->default(0)
                            ->prefixIcon('heroicon-o-shield-check'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'DRAFT' => 'Draft',
                                'INREVIEW' => 'In Review',
                                'APPROVED' => 'Approved',
                                'REVOKED' => 'Revoked',
                            ])
                            ->default('DRAFT')
                            ->required()
                            ->prefixIcon('heroicon-o-flag'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Molecule')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-arrow-up-circle')
                            ->helperText('Parent molecule if this is a variant'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Flags & Properties')
                    ->description('Molecular properties and flags')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->helperText('Is this molecule record active?')
                            ->default(false),

                        Forms\Components\Toggle::make('is_parent')
                            ->label('Is Parent')
                            ->helperText('Is this the parent structure?')
                            ->default(false),

                        Forms\Components\Toggle::make('has_variants')
                            ->label('Has Variants')
                            ->helperText('Does this molecule have variants?')
                            ->default(false),

                        Forms\Components\Toggle::make('has_stereo')
                            ->label('Has Stereochemistry')
                            ->helperText('Does this molecule have stereochemistry?')
                            ->default(false),

                        Forms\Components\Toggle::make('is_tautomer')
                            ->label('Is Tautomer')
                            ->helperText('Is this a tautomeric form?')
                            ->default(false),

                        Forms\Components\Toggle::make('is_placeholder')
                            ->label('Is Placeholder')
                            ->helperText('Is this a placeholder entry?')
                            ->default(false),

                        Forms\Components\TextInput::make('variants_count')
                            ->label('Variants Count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Number of variants'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Notes')
                    ->description('Additional structural comments')
                    ->schema([
                        Forms\Components\Textarea::make('structural_comments')
                            ->label('Structural Comments')
                            ->maxLength(5000)
                            ->rows(4)
                            ->placeholder('Additional structural or chemical comments...')
                            ->helperText('Optional structural notes and comments')
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
                        if (empty($record->canonical_smiles)) {
                            return null;
                        }

                        return env('CM_PUBLIC_API', 'https://api.cheminf.studio/latest/')
                            .'depict/2D?smiles='.urlencode($record->canonical_smiles)
                            .'&height=200&width=200&CIP=true&toolkit=cdk';
                    })
                    ->width(120)
                    ->height(120)
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Compound Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-beaker')
                    ->description(fn ($record) => $record->identifier)
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name)
                    ->copyable()
                    ->copyMessage('Compound name copied!'),

                Tables\Columns\TextColumn::make('identifier')
                    ->label('Identifier')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('canonical_smiles')
                    ->label('SMILES')
                    ->searchable()
                    ->fontFamily('mono')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->canonical_smiles)
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT' => 'warning',
                        'INREVIEW' => 'info',
                        'APPROVED' => 'success',
                        'REVOKED' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'DRAFT' => 'heroicon-o-pencil',
                        'INREVIEW' => 'heroicon-o-clock',
                        'APPROVED' => 'heroicon-o-check-circle',
                        'REVOKED' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('annotation_level')
                    ->label('Annotation')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        0 => 'gray',
                        1 => 'warning',
                        2 => 'info',
                        3 => 'success',
                        4 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => "Level {$state}")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('has_variants')
                    ->label('Variants')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('has_stereo')
                    ->label('Stereo')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'DRAFT' => 'Draft',
                        'INREVIEW' => 'In Review',
                        'APPROVED' => 'Approved',
                        'REVOKED' => 'Revoked',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active Status')
                    ->placeholder('All molecules')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\TernaryFilter::make('has_variants')
                    ->label('Has Variants')
                    ->placeholder('All molecules')
                    ->trueLabel('With variants')
                    ->falseLabel('Without variants'),

                Tables\Filters\TernaryFilter::make('has_stereo')
                    ->label('Has Stereochemistry')
                    ->placeholder('All molecules')
                    ->trueLabel('With stereochemistry')
                    ->falseLabel('Without stereochemistry'),

                Tables\Filters\SelectFilter::make('annotation_level')
                    ->label('Annotation Level')
                    ->options([
                        0 => 'Level 0',
                        1 => 'Level 1',
                        2 => 'Level 2',
                        3 => 'Level 3',
                        4 => 'Level 4',
                    ])
                    ->multiple(),
            ])
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label('Status')
                    ->collapsible(),

                Tables\Grouping\Group::make('annotation_level')
                    ->label('Annotation Level')
                    ->getTitleFromRecordUsing(fn ($record) => "Annotation Level {$record->annotation_level}")
                    ->collapsible(),

                Tables\Grouping\Group::make('active')
                    ->label('Active Status')
                    ->getTitleFromRecordUsing(fn ($record) => $record->active ? 'Active Molecules' : 'Inactive Molecules')
                    ->collapsible(),
            ])
            ->defaultGroup('status')
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label('')
                    ->tooltip(fn ($record) => $record->active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['active' => ! $record->active]);
                        \Filament\Notifications\Notification::make()
                            ->title('Molecule updated')
                            ->body('The molecule has been '.($record->active ? 'activated' : 'deactivated').'.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->modalHeading('Molecule Details'),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this molecule? This action cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['status' => 'APPROVED']));
                            \Filament\Notifications\Notification::make()
                                ->title('Molecules approved')
                                ->body("{$count} molecule(s) have been approved.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['active' => true]));
                            \Filament\Notifications\Notification::make()
                                ->title('Molecules activated')
                                ->body("{$count} molecule(s) have been activated.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected molecules? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListMolecules::route('/'),
            'create' => Pages\CreateMolecule::route('/create'),
            'edit' => Pages\EditMolecule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('active', true)->count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'identifier', 'canonical_smiles', 'standard_inchi_key', 'iupac_name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Identifier' => $record->identifier ?? 'No identifier',
            'Status' => $record->status,
            'SMILES' => $record->canonical_smiles ? substr($record->canonical_smiles, 0, 50) : 'No SMILES',
        ];
    }
}
