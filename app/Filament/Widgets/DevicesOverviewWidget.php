<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DevicesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Device::query()->withCount('samples')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Device Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Manufacturer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model_no')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'heroicon-o-signal',
                        'INACTIVE' => 'heroicon-o-x-circle',
                        'MAINTENANCE' => 'heroicon-o-wrench-screwdriver',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        'MAINTENANCE' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (string $state): string => ucfirst(strtolower($state)))
                    ->action(
                        Tables\Actions\Action::make('toggleStatus')
                            ->label('Toggle Status')
                            ->requiresConfirmation()
                            ->modalHeading(fn (Device $record): string => "Toggle Status for {$record->name}")
                            ->modalDescription(fn (Device $record): string => $record->status === 'ACTIVE'
                                    ? 'Are you sure you want to deactivate this device?'
                                    : 'Are you sure you want to activate this device?'
                            )
                            ->modalSubmitActionLabel('Yes, change status')
                            ->action(function (Device $record) {
                                $record->status = $record->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
                                $record->save();
                            })
                    ),

                Tables\Columns\TextColumn::make('samples_count')
                    ->label('Total Samples')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('name');
    }

    protected function getTableHeading(): string
    {
        return 'Devices Overview';
    }
}
