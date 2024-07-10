<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SampleResource\Pages;
use App\Models\Sample;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-m-cube-transparent';

    protected static ?string $navigationGroup = 'Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('device', 'name'),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Forms\Components\TextInput::make('identifier')
                    ->maxLength(255),
                Forms\Components\Select::make('solvent_id')
                    ->relationship('solvent', 'name'),
                Forms\Components\Select::make('molecule_id')
                    ->relationship('molecule', 'name'),
                Forms\Components\TextInput::make('spectrum_type')
                    ->maxLength(255),
                Forms\Components\Textarea::make('instructions')
                    ->columnSpanFull(),
                Forms\Components\Select::make('featured_image_id')
                    ->relationship('featuredImage', 'id'),
                Forms\Components\TextInput::make('priority')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('operator_id')
                    ->relationship('operator', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identifier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('solvent.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('molecule.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spectrum_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('featuredImage.id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->searchable(),
                Tables\Columns\TextColumn::make('operator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'edit' => Pages\EditSample::route('/{record}/edit'),
        ];
    }
}
