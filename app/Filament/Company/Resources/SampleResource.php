<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\SampleResource\Pages;
use App\Models\Device;
use App\Models\Sample;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;
use Maartenpaauw\Filament\ModelStates\StateColumn;
use Maartenpaauw\Filament\ModelStates\StateSelectColumn;
use Maartenpaauw\Filament\ModelStates\StateTableAction;
use App\States\Sample\SubmittedState;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-m-cube-transparent';

    protected static ?string $navigationGroup = 'Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Sample::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(Sample::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // StateTableAction::make('submit')
                //     ->transitionTo(SubmittedState::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('submit')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->status = new SubmittedState($record);
                                $record->save();
                            }
                        })
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
