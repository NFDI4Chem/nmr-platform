<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SpectrumTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'spectrumTypes';

    protected static ?string $title = 'Spectrum Types';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Spectrum Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., 1H NMR, 13C NMR')
                    ->helperText('Name of the spectrum type'),

                Forms\Components\TextInput::make('neuclei')
                    ->label('Nuclei')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., 1H, 13C, 31P')
                    ->helperText('Nuclei observed in this spectrum type'),

                Forms\Components\Select::make('dimentionality')
                    ->label('Dimensionality')
                    ->options([
                        '1D' => '1D',
                        '2D' => '2D',
                    ])
                    ->required()
                    ->default('1D')
                    ->helperText('Spectrum dimensionality'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(1000)
                    ->rows(3)
                    ->placeholder('Additional information about this spectrum type...')
                    ->helperText('Optional description'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Spectrum Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-chart-bar'),

                Tables\Columns\TextColumn::make('neuclei')
                    ->label('Nuclei')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('dimentionality')
                    ->label('Dimension')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1D' => 'success',
                        '2D' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('dimentionality')
                    ->label('Dimensionality')
                    ->options([
                        '1D' => '1D',
                        '2D' => '2D',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Attach Spectrum Type'),
                Tables\Actions\CreateAction::make()
                    ->label('Create Spectrum Type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No spectrum types')
            ->emptyStateDescription('Attach or create spectrum types that this device can perform.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
