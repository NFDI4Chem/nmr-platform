<?php

namespace App\Filament\Company\Resources\MoleculeResource\RelationManagers;

use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SamplesRelationManager extends RelationManager
{
    protected static string $relationship = 'samples';

    protected static ?string $title = 'Related Samples';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // No form needed for viewing
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('company_id', Filament::getTenant()?->getKey())
                ->with(['device', 'solvent', 'operator', 'spectrumTypes'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Sample ID')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Sample $record): string => $record->created_at->diffForHumans()),

                Tables\Columns\TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('solvent.name')
                    ->label('Solvent')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('spectrumTypes.name')
                    ->label('Spectrum Types')
                    ->badge()
                    ->separator(',')
                    ->limit(2),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOW' => 'gray',
                        'MEDIUM' => 'info',
                        'HIGH' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        'Draft' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('operator.name')
                    ->label('Operator')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'LOW' => 'Low',
                        'MEDIUM' => 'Medium',
                        'HIGH' => 'High',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                // No create action - samples are created separately
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Sample $record): string => \App\Filament\Company\Resources\SampleResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->defaultSort('created_at', 'desc');
    }
}
