<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SampleResource\Pages;
use App\Models\Sample;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Samples';

    protected static ?string $modelLabel = 'Sample';

    protected static ?string $pluralModelLabel = 'Samples';

    protected static ?string $navigationGroup = 'Sample Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tracking Information')
                    ->description('Sample identification and tracking details')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Sample ID')
                            ->placeholder('Enter a keyword for your personal use')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Unique identifier for this sample'),

                        Forms\Components\TextInput::make('personal_key')
                            ->label('Personal Key')
                            ->maxLength(255)
                            ->placeholder('Your personal reference')
                            ->prefixIcon('heroicon-o-key')
                            ->helperText('Optional personal identifier'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Device & Configuration')
                    ->description('Device assignment and spectrum configuration')
                    ->schema([
                        Forms\Components\Select::make('device_id')
                            ->label('Device')
                            ->relationship('device', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('manufacturer')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('model_no')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->prefixIcon('heroicon-o-cpu-chip')
                            ->helperText('Select the NMR device'),

                        Forms\Components\Select::make('spectrumTypes')
                            ->label('Spectrum Types')
                            ->relationship('spectrumTypes', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-chart-bar')
                            ->helperText('Select spectrum types to be performed'),

                        Forms\Components\TextInput::make('other_nuclei')
                            ->label('Other Nuclei')
                            ->maxLength(255)
                            ->placeholder('Please specify other nuclei')
                            ->prefixIcon('heroicon-o-cube')
                            ->helperText('Additional nuclei not covered by standard spectrum types'),

                        Forms\Components\Toggle::make('automation')
                            ->label('Automation')
                            ->helperText('Process this sample automatically')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Structure Information')
                    ->description('Molecular structure and compound details')
                    ->schema([
                        Forms\Components\Select::make('molecule_id')
                            ->label('Molecule')
                            ->relationship('molecule', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Select or create a molecule'),

                        Forms\Components\FileUpload::make('molfile_id')
                            ->label('MOL File')
                            ->acceptedFileTypes(['chemical/x-mdl-molfile', 'chemical/x-mol'])
                            ->maxSize(1024)
                            ->helperText('Upload molecular structure file'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Sample Details')
                    ->description('Solvent, priority, and additional information')
                    ->schema([
                        Forms\Components\Select::make('solvent_id')
                            ->label('Solvent')
                            ->relationship('solvent', 'name', fn ($query) => $query->where('active', true))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-beaker')
                            ->helperText('Select the NMR solvent'),

                        Forms\Components\Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'LOW' => 'Low',
                                'MEDIUM' => 'Medium',
                                'HIGH' => 'High',
                                'URGENT' => 'Urgent',
                            ])
                            ->default('LOW')
                            ->required()
                            ->prefixIcon('heroicon-o-flag')
                            ->helperText('Sample processing priority'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->default('Draft')
                            ->required()
                            ->prefixIcon('heroicon-o-flag')
                            ->helperText('Current sample status'),

                        Forms\Components\Select::make('operator_id')
                            ->label('Operator')
                            ->relationship('operator', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Assigned operator'),

                        Forms\Components\Textarea::make('instructions')
                            ->label('Special Instructions')
                            ->maxLength(5000)
                            ->rows(4)
                            ->placeholder('Special care for sample...')
                            ->helperText('Additional instructions or notes for sample handling')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('additional_infofile_id')
                            ->label('Additional Information File')
                            ->acceptedFileTypes(['application/pdf', 'text/plain', 'image/*'])
                            ->maxSize(2048)
                            ->helperText('Upload additional documentation'),

                        Forms\Components\FileUpload::make('rawdata_file_id')
                            ->label('Raw Data File')
                            ->maxSize(10240)
                            ->helperText('Upload raw NMR data file'),

                        Forms\Components\Textarea::make('comments')
                            ->label('Comments')
                            ->maxLength(5000)
                            ->rows(3)
                            ->placeholder('Additional comments...')
                            ->helperText('Additional comments or observations')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Sample ID')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-beaker')
                    ->copyable()
                    ->copyMessage('Sample ID copied!'),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hidden(function () {
                        return Filament::getTenant() ? true : false;
                    }),

                Tables\Columns\TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cpu-chip')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('solvent.name')
                    ->label('Solvent')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'URGENT' => 'danger',
                        'HIGH' => 'warning',
                        'MEDIUM' => 'success',
                        'LOW' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'URGENT' => 'heroicon-o-arrow-up',
                        'HIGH' => 'heroicon-o-arrow-up',
                        'MEDIUM' => 'heroicon-o-minus',
                        'LOW' => 'heroicon-o-arrow-down',
                        default => 'heroicon-o-minus',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'Draft' => 'warning',
                        'approved' => 'success',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'submitted' => 'heroicon-o-paper-airplane',
                        'Draft' => 'heroicon-o-pencil',
                        'approved' => 'heroicon-o-check-circle',
                        'completed' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('spectrumTypes.name')
                    ->label('Spectrum Types')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('automation')
                    ->label('Auto')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('operator.name')
                    ->label('Operator')
                    ->searchable()
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
                        'Draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priority')
                    ->options([
                        'URGENT' => 'Urgent',
                        'HIGH' => 'High',
                        'MEDIUM' => 'Medium',
                        'LOW' => 'Low',
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

                Tables\Filters\TernaryFilter::make('automation')
                    ->label('Automation')
                    ->placeholder('All samples')
                    ->trueLabel('Automated only')
                    ->falseLabel('Manual only'),
            ])
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label('Status')
                    ->collapsible(),

                Tables\Grouping\Group::make('priority')
                    ->label('Priority')
                    ->collapsible(),

                Tables\Grouping\Group::make('device.name')
                    ->label('Device')
                    ->collapsible(),
            ])
            ->defaultGroup('status')
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'submitted')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        \Filament\Notifications\Notification::make()
                            ->title('Sample approved')
                            ->body("Sample '{$record->reference}' has been approved.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'submitted')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        \Filament\Notifications\Notification::make()
                            ->title('Sample rejected')
                            ->body("Sample '{$record->reference}' has been rejected.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->modalHeading('Sample Details'),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this sample? This action cannot be undone.'),
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
                            $records->each(fn ($record) => $record->update(['status' => 'approved']));
                            \Filament\Notifications\Notification::make()
                                ->title('Samples approved')
                                ->body("{$count} sample(s) have been approved.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['status' => 'completed']));
                            \Filament\Notifications\Notification::make()
                                ->title('Samples completed')
                                ->body("{$count} sample(s) have been marked as completed.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected samples? This action cannot be undone.'),
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
            'index' => Pages\ListSamples::route('/'),
            'create' => Pages\CreateSample::route('/create'),
            'edit' => Pages\EditSample::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'submitted')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'submitted')->count();

        return $count > 0 ? 'warning' : 'gray';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'personal_key', 'instructions'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Device' => $record->device?->name ?? 'No device',
            'Status' => $record->status,
            'Priority' => $record->priority,
        ];
    }
}
