<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\SampleResource\Pages;
use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Laboratory';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->where('company_id', $tenant?->getKey())
            ->with(['device', 'solvent', 'molecule', 'operator', 'spectrumTypes']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Sample::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Sample ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Sample $record): string => $record->created_at->diffForHumans())
                    ->tooltip(fn (Sample $record): string => $record->created_at->format('M d, Y H:i')),

                Tables\Columns\ImageColumn::make('molecule.structure')
                    ->label('Structure')
                    ->state(function (Sample $record) {
                        if (!$record->molecule || empty($record->molecule->canonical_smiles)) {
                            return null;
                        }

                        return env('CM_PUBLIC_API', 'https://api.cheminf.studio/latest/')
                            .'depict/2D?smiles='.urlencode($record->molecule->canonical_smiles)
                            .'&height=120&width=120&CIP=true&toolkit=cdk';
                    })
                    ->width(80)
                    ->height(80)
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('molecule.name')
                    ->label('Molecule')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('analysis_details')
                    ->label('Analysis Details')
                    ->html()
                    ->searchable(['device.name', 'solvent.name'])
                    ->state(function (Sample $record): string {
                        $parts = [];
                        
                        if ($record->device) {
                            $parts[] = '<div class="flex items-center gap-1"><span class="font-semibold text-gray-900 dark:text-gray-100">' . e($record->device->name) . '</span></div>';
                        }
                        
                        if ($record->solvent) {
                            $parts[] = '<div class="flex items-center gap-1"><span class="text-xs text-gray-500 dark:text-gray-400">Solvent:</span> <span class="text-gray-700 dark:text-gray-300">' . e($record->solvent->name) . '</span></div>';
                        }
                        
                        if ($record->spectrumTypes && $record->spectrumTypes->isNotEmpty()) {
                            $spectrums = $record->spectrumTypes->pluck('name')->map(fn($name) => e($name))->join(', ');
                            $parts[] = '<div class="flex items-center gap-1"><span class="text-xs text-gray-500 dark:text-gray-400">Types:</span> <span class="text-sm text-gray-600 dark:text-gray-400">' . $spectrums . '</span></div>';
                        }
                        
                        return implode('', $parts);
                    })
                    ->wrap(),

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
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
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

                Tables\Filters\SelectFilter::make('device_id')
                    ->label('Device')
                    ->relationship('device', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('solvent_id')
                    ->label('Solvent')
                    ->relationship('solvent', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Sample'),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Sample'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Sample'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'view' => Pages\ViewSample::route('/{record}'),
            'edit' => Pages\EditSample::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return null;
        }

        return static::getModel()::where('company_id', $tenant->getKey())->count();
    }
}
