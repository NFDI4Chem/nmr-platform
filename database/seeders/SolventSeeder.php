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
                'name' => 'Chloroform-d',
                'description' => 'Common NMR solvent for organic compounds',
                'molecular_formula' => 'CDCl3',
                'molecular_weight' => 119.38,
                'meta_data' => ['uses' => 'Common NMR solvent for organic compounds'],
                'active' => true,
            ],
            [
                'name' => 'DMSO-d6',
                'description' => 'Used for polar compounds in NMR',
                'molecular_formula' => 'C2H6OS',
                'molecular_weight' => 84.17,
                'meta_data' => ['uses' => 'Used for polar compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Methanol-d4',
                'description' => 'Methanol-d4 is used for a variety of NMR experiments',
                'molecular_formula' => 'CD3OD',
                'molecular_weight' => 36.06,
                'meta_data' => ['uses' => 'Methanol-d4 is used for a variety of NMR experiments'],
                'active' => true,
            ],
            [
                'name' => 'Water-d2',
                'description' => 'Used as a solvent and also as a reference in NMR',
                'molecular_formula' => 'D2O',
                'molecular_weight' => 20.03,
                'meta_data' => ['uses' => 'Used as a solvent and also as a reference in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Acetone-d6',
                'description' => 'Common solvent for polar aprotic compounds in NMR',
                'molecular_formula' => 'C3D6O',
                'molecular_weight' => 64.12,
                'meta_data' => ['uses' => 'Common solvent for polar aprotic compounds in NMR'],
                'active' => true,
            ],
            [
                'name' => 'Benzene-d6',
                'description' => 'Used for aromatic compounds in NMR',
                'molecular_formula' => 'C6D6',
                'molecular_weight' => 84.15,
                'meta_data' => ['uses' => 'Used for aromatic compounds in NMR'],
                'active' => true,
            ],
        ];

        foreach ($solvents as $solvent) {
            Solvent::create($solvent);
        }

        $this->command->info('Successfully seeded '.count($solvents).' NMR solvents.');
    }
}
