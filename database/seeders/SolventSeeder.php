<?php

namespace Database\Seeders;

use App\Models\Solvent;
use Illuminate\Database\Seeder;

class SolventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing solvents first
        Solvent::truncate();

        $solvents = [
            [
                'name' => 'Acetone-d6',
                'description' => 'Common solvent for polar aprotic compounds in NMR',
                'molecular_formula' => 'C₃D₆O',
                'molecular_weight' => 64.12,
                'meta_data' => ['uses' => 'Common solvent for polar aprotic compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'DMSO-d6',
                'description' => 'Used for polar compounds in NMR',
                'molecular_formula' => 'C₂D₆OS',
                'molecular_weight' => 84.17,
                'meta_data' => ['uses' => 'Used for polar compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Chloroform-d',
                'description' => 'Common NMR solvent for organic compounds',
                'molecular_formula' => 'CDCl₃',
                'molecular_weight' => 120.38,
                'meta_data' => ['uses' => 'Common NMR solvent for organic compounds'],
                'active' => true,
            ],
            [
                'name' => 'Dichloromethane-d2',
                'description' => 'Common NMR solvent for moderately polar compounds',
                'molecular_formula' => 'CD₂Cl₂',
                'molecular_weight' => 86.94,
                'meta_data' => ['uses' => 'Common NMR solvent for moderately polar compounds'],
                'active' => true,
            ],
            [
                'name' => 'Acetonitrile-d3',
                'description' => 'Polar aprotic solvent for NMR spectroscopy',
                'molecular_formula' => 'C₂D₃N',
                'molecular_weight' => 44.07,
                'meta_data' => ['uses' => 'Polar aprotic solvent for NMR spectroscopy'],
                'active' => true,
            ],
            [
                'name' => 'Methanol-d4',
                'description' => 'Methanol-d4 is used for a variety of NMR experiments',
                'molecular_formula' => 'CD₃OD',
                'molecular_weight' => 36.07,
                'meta_data' => ['uses' => 'Methanol-d4 is used for a variety of NMR experiments'],
                'active' => true,
            ],
            [
                'name' => 'Chlorobenzene-d5',
                'description' => 'Used for aromatic and non-polar compounds in NMR',
                'molecular_formula' => 'C₆D₅Cl',
                'molecular_weight' => 117.57,
                'meta_data' => ['uses' => 'Used for aromatic and non-polar compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Benzene-d6',
                'description' => 'Used for aromatic compounds in NMR',
                'molecular_formula' => 'C₆D₆',
                'molecular_weight' => 84.15,
                'meta_data' => ['uses' => 'Used for aromatic compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Deuterium Oxide',
                'description' => 'Used as a solvent and also as a reference in NMR',
                'molecular_formula' => 'D₂O',
                'molecular_weight' => 20.03,
                'meta_data' => ['uses' => 'Used as a solvent and also as a reference in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Trifluoroethanol-d3',
                'description' => 'Highly polar solvent used in NMR spectroscopy',
                'molecular_formula' => 'CF₃CD₂OH',
                'molecular_weight' => 103.05,
                'meta_data' => ['uses' => 'Highly polar solvent used in NMR spectroscopy'],
                'active' => true,
            ],
            [
                'name' => 'Tetrahydrofuran-d8',
                'description' => 'Common solvent for organometallic and polymer NMR',
                'molecular_formula' => 'C₄D₈O',
                'molecular_weight' => 80.15,
                'meta_data' => ['uses' => 'Common solvent for organometallic and polymer NMR'],
                'active' => true,
            ],
            [
                'name' => 'Toluene-d8',
                'description' => 'Used for aromatic and non-polar compounds in NMR',
                'molecular_formula' => 'C₇D₈',
                'molecular_weight' => 100.19,
                'meta_data' => ['uses' => 'Used for aromatic and non-polar compounds in NMR'],
                'active' => true,
            ],
        ];

        foreach ($solvents as $solvent) {
            Solvent::create($solvent);
        }

        $this->command->info('Successfully seeded '.count($solvents).' NMR solvents.');
    }
}
