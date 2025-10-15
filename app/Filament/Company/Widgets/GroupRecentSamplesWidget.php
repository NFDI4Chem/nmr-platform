<?php

namespace App\Filament\Company\Widgets;

use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class GroupRecentSamplesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return $table
                ->query(Sample::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        $companyId = $tenant->getKey();

        return $table
            ->query(
                Sample::query()
                    ->where('company_id', $companyId)
                    ->with(['solvent', 'molecule', 'device', 'operator'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Sample ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('device.name')
                    ->label('Device')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('molecule.name')
                    ->label('Molecule')
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('solvent.name')
                    ->label('Solvent')
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableHeading(): string
    {
        return 'Recent Samples';
    }
}
