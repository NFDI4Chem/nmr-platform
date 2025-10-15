<?php

namespace Database\Seeders;

use App\Models\Impurity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ImpuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing impurities
        Impurity::truncate();

        // Load Proton (1H) impurities
        $protonImpuritiesPath = database_path('seeders/impurities/ProtonImpurities.json');
        if (File::exists($protonImpuritiesPath)) {
            $protonImpurities = json_decode(File::get($protonImpuritiesPath), true);

            if ($protonImpurities && is_array($protonImpurities)) {
                foreach ($protonImpurities as $impurity) {
                    Impurity::create([
                        'names' => $impurity['names'] ?? [],
                        'smiles' => $impurity['smiles'] ?? null,
                        'ranges' => $impurity['ranges'] ?? [],
                        'nucleus' => $impurity['nucleus'] ?? '1H',
                        'solvent' => $impurity['solvent'] ?? '',
                        'active' => true,
                    ]);
                }
                $this->command->info('Loaded '.count($protonImpurities).' proton (1H) impurities.');
            }
        } else {
            $this->command->warn('ProtonImpurities.json file not found.');
        }

        // Load Carbon (13C) impurities
        $carbonImpuritiesPath = database_path('seeders/impurities/CarbonImpurities.json');
        if (File::exists($carbonImpuritiesPath)) {
            $carbonImpurities = json_decode(File::get($carbonImpuritiesPath), true);

            if ($carbonImpurities && is_array($carbonImpurities)) {
                foreach ($carbonImpurities as $impurity) {
                    Impurity::create([
                        'names' => $impurity['names'] ?? [],
                        'smiles' => $impurity['smiles'] ?? null,
                        'ranges' => $impurity['ranges'] ?? [],
                        'nucleus' => $impurity['nucleus'] ?? '13C',
                        'solvent' => $impurity['solvent'] ?? '',
                        'active' => true,
                    ]);
                }
                $this->command->info('Loaded '.count($carbonImpurities).' carbon (13C) impurities.');
            }
        } else {
            $this->command->warn('CarbonImpurities.json file not found.');
        }

        $totalCount = Impurity::count();
        $this->command->info("Total impurities seeded: {$totalCount}");
    }
}
