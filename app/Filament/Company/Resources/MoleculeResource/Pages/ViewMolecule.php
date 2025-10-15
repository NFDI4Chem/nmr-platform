<?php

namespace App\Filament\Company\Resources\MoleculeResource\Pages;

use App\Filament\Company\Resources\MoleculeResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;

class ViewMolecule extends ViewRecord
{
    protected static string $resource = MoleculeResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Molecule Structure')
                    ->schema([
                        Infolists\Components\ImageEntry::make('structure')
                            ->label('')
                            ->state(function ($record) {
                                if (empty($record->canonical_smiles)) {
                                    return null;
                                }

                                return env('CM_PUBLIC_API', 'https://api.cheminf.studio/latest/')
                                    .'depict/2D?smiles='.urlencode($record->canonical_smiles)
                                    .'&height=400&width=400&CIP=true&toolkit=cdk';
                            })
                            ->width(400)
                            ->height(400)
                            ->defaultImageUrl(url('/images/placeholder.png'))
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Compound Name'),

                        Infolists\Components\TextEntry::make('identifier')
                            ->label('Identifier'),

                        Infolists\Components\TextEntry::make('iupac_name')
                            ->label('IUPAC Name')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('molecular_formula')
                            ->label('Molecular Formula')
                            ->fontFamily('mono'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Chemical Structure Representations')
                    ->schema([
                        Infolists\Components\TextEntry::make('canonical_smiles')
                            ->label('Canonical SMILES')
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('standard_inchi')
                            ->label('Standard InChI')
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('standard_inchi_key')
                            ->label('Standard InChI Key')
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Related Samples')
                    ->schema([
                        Infolists\Components\TextEntry::make('samples_count')
                            ->label('Number of Samples')
                            ->state(function ($record) {
                                return $record->samples()
                                    ->where('company_id', \Filament\Facades\Filament::getTenant()?->getKey())
                                    ->count();
                            })
                            ->badge()
                            ->color('primary'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // No edit action
        ];
    }
}

