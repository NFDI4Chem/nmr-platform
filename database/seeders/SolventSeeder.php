<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $solvents = [
            [
                'name' => 'Chloroform-d',
                'description' => 'Common NMR solvent for organic compounds',
                'molecular_formula' => 'CDCl3',
                'molecular_weight' => 119.38,
                'meta_data' => json_encode(['uses' => 'Common NMR solvent for organic compounds']),
            ],
            [
                'name' => 'DMSO-d6',
                'description' => 'Used for polar compounds in NMR',
                'molecular_formula' => 'C2H6OS',
                'molecular_weight' => 84.17,
                'meta_data' => json_encode(['uses' => 'Used for polar compounds in NMR']),
            ],
            [
                'name' => 'Methanol-d4',
                'description' => 'Methanol-d4 is used for a variety of NMR experiments',
                'molecular_formula' => 'CD3OD',
                'molecular_weight' => 36.06,
                'meta_data' => json_encode(['uses' => 'Methanol-d4 is used for a variety of NMR experiments']),
            ],
            [
                'name' => 'Water-d2',
                'description' => 'Used as a solvent and also as a reference in NMR',
                'molecular_formula' => 'D2O',
                'molecular_weight' => 20.03,
                'meta_data' => json_encode(['uses' => 'Used as a solvent and also as a reference in NMR']),
            ],
            [
                'name' => 'Acetone-d6',
                'description' => 'Common solvent for polar aprotic compounds in NMR',
                'molecular_formula' => 'C3D6O',
                'molecular_weight' => 64.12,
                'meta_data' => json_encode(['uses' => 'Common solvent for polar aprotic compounds in NMR']),
            ],
            [
                'name' => 'Benzene-d6',
                'description' => 'Used for aromatic compounds in NMR',
                'molecular_formula' => 'C6D6',
                'molecular_weight' => 84.15,
                'meta_data' => json_encode(['uses' => 'Used for aromatic compounds in NMR']),
            ],
        ];

        DB::table('solvents')->insert($solvents);
    }
}
