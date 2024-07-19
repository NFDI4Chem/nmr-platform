<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SampleResource\Pages;
use App\Models\Sample;
use App\States\Sample\ToRejected;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maartenpaauw\Filament\ModelStates\StateTableAction;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-m-cube-transparent';

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
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('reject')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('comments')
                                ->label('Reject reason')
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Sample $record): void {
                            $record->comments = $data['comments'];
                            $record->save();
                        }),
                    Tables\Actions\Action::make('finish')
                        ->requiresConfirmation()
                        ->form([
                            MediaPicker::make('finished_file_id')
                                ->label('Choose the file')
                                ->required(),
                        ])
                        ->action(function (array $data, Sample $record): void {
                            // Featured image details for the record
                            $filament_media_library_item = DB::table('filament_media_library')->find($record->featured_image_id);
                            $company_id = $filament_media_library_item->company_id;
                            // $user_id = $filament_media_library_item->user_id;
                            $folder_id = $filament_media_library_item->folder_id;

                            // Save the record to get the file's filament_media_library_item id
                            $record->finished_file_id = $data['finished_file_id'];
                            $record->save();

                            // Place the file in the folder of the user (use detials from above)
                            DB::table('filament_media_library')
                                ->where('id', $record->finished_file_id)
                                ->update(['company_id' => $company_id,
                                    'user_id' => Auth::user()->id,
                                    'folder_id' => $folder_id]);
                            // $filament_media_library_item = DB::table('filament_media_library')->where('id', $record->finished_file_id)->get()[0];
                            // dd($filament_media_library_item);
                            // $filament_media_library_item->company_id = $company_id;
                            // $filament_media_library_item->user_id = Auth::user()->id;
                            // $filament_media_library_item->folder_id = $folder_id;
                            // $filament_media_library_item->save();
                        }),
                    // StateTableAction::make('reject')
                    //     ->transitionTo(ToRejected::class),
                ]),

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
