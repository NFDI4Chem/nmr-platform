<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpectrumTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $spectrumTypes = [
            [
                'name' => 'Proton NMR (1H NMR)',
                'neuclei' => '1H',
                'description' => 'Provides information about the hydrogen atoms in a molecule',
                'dimentionality' => '1D',
            ],
            [
                'name' => 'Carbon-13 NMR (13C NMR)',
                'neuclei' => '13C',
                'description' => 'Provides information about the carbon atoms in a molecule',
                'dimentionality' => '1D',
            ],
            [
                'name' => 'COSY (COrrelation SpectroscopY)',
                'neuclei' => '1H',
                'description' => 'Correlates proton-proton interactions, useful for identifying coupling between protons',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'HSQC (Heteronuclear Single Quantum Coherence)',
                'neuclei' => '1H-13C',
                'description' => 'Correlates protons with directly bonded carbons or other heteroatoms',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'HMBC (Heteronuclear Multiple Bond Correlation)',
                'neuclei' => '1H-13C',
                'description' => 'Correlates protons with carbons two or three bonds away',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'NOESY (Nuclear Overhauser Effect SpectroscopY)',
                'neuclei' => '1H',
                'description' => 'Provides information on spatial proximity between protons',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'TOCSY (Total Correlation SpectroscopY)',
                'neuclei' => '1H',
                'description' => 'Correlates all protons within a spin system',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'HSQC-TOCSY',
                'neuclei' => '1H-13C',
                'description' => 'Combines HSQC and TOCSY experiments',
                'dimentionality' => '2D',
            ],
            [
                'name' => 'HSQC-NOESY',
                'neuclei' => '1H-13C',
                'description' => 'Combines HSQC and NOESY experiments',
                'dimentionality' => '2D',
            ],
        ];

        DB::table('spectrum_types')->insert($spectrumTypes);
    }
}
