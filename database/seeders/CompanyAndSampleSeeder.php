<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Device;
use App\Models\Molecule;
use App\Models\Sample;
use App\Models\Solvent;
use App\Models\SpectrumType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanyAndSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@email.com')->first();

        if (! $superAdmin) {
            $this->command->error('Super admin user not found! Please run the main DatabaseSeeder first.');

            return;
        }

        $this->command->info('Creating personal company for super_admin...');

        // First, create a personal company for the super_admin if they don't have one
        $personalCompany = $superAdmin->ownedCompanies()->where('personal_company', true)->first();

        if (! $personalCompany) {
            $personalCompany = Company::create([
                'user_id' => $superAdmin->id,
                'name' => $superAdmin->name."'s Company",
                'slug' => Str::slug($superAdmin->name.'-company'),
                'search_slug' => Str::slug($superAdmin->name.'-company'),
                'personal_company' => true,
                'reference' => strtoupper(Str::random(3)).rand(1000, 9999),
            ]);
            $this->command->info("Created personal company: {$personalCompany->name}");
        } else {
            $this->command->info("Personal company already exists: {$personalCompany->name}");
        }

        $this->command->info('Creating research groups for super_admin...');

        // Create 5 research groups
        $companies = [
            [
                'name' => 'Computational Chemistry Research Group',
                'description' => 'Advanced research in computational methods for molecular design and drug discovery.',
                'faculty' => 'Faculty of Chemistry and Earth Sciences',
                'institute' => 'Institute for Computational Chemistry',
                'leader_name' => 'Prof. Dr. Maria Schmidt',
                'research_keywords' => 'Computational Chemistry, Molecular Modeling, Quantum Chemistry, Machine Learning',
            ],
            [
                'name' => 'Analytical NMR Spectroscopy Laboratory',
                'description' => 'Specialized facility for high-resolution NMR spectroscopy and structure elucidation.',
                'faculty' => 'Faculty of Natural Sciences',
                'institute' => 'Institute for Analytical Chemistry',
                'leader_name' => 'Prof. Dr. Thomas Wagner',
                'research_keywords' => 'NMR Spectroscopy, Structure Elucidation, Analytical Chemistry, Metabolomics',
            ],
            [
                'name' => 'Organic Synthesis Research Group',
                'description' => 'Focus on total synthesis of natural products and novel synthetic methodologies.',
                'faculty' => 'Faculty of Chemistry',
                'institute' => 'Institute of Organic Chemistry',
                'leader_name' => 'Prof. Dr. Anna Müller',
                'research_keywords' => 'Organic Synthesis, Catalysis, Natural Products, Drug Discovery',
            ],
            [
                'name' => 'Materials Science and Nanotechnology Lab',
                'description' => 'Research on novel materials, nanostructures, and their applications.',
                'faculty' => 'Faculty of Science and Technology',
                'institute' => 'Institute of Materials Science',
                'leader_name' => 'Prof. Dr. Klaus Becker',
                'research_keywords' => 'Materials Science, Nanotechnology, Surface Chemistry, Electrochemistry',
            ],
            [
                'name' => 'Biochemistry and Enzyme Engineering Group',
                'description' => 'Study of enzyme mechanisms and development of biocatalytic processes.',
                'faculty' => 'Faculty of Chemistry and Earth Sciences',
                'institute' => 'Institute of Biochemistry',
                'leader_name' => 'Prof. Dr. Sophie Weber',
                'research_keywords' => 'Biochemistry, Enzymology, Protein Chemistry, Chemical Biology',
            ],
        ];

        $createdCompanies = [];

        foreach ($companies as $companyData) {
            $company = Company::create([
                'user_id' => $superAdmin->id,
                'name' => $companyData['name'],
                'description' => $companyData['description'],
                'slug' => Str::slug($companyData['name']),
                'search_slug' => Str::slug($companyData['name']),
                'personal_company' => false,
                'reference' => strtoupper(Str::random(3)).rand(1000, 9999),
                'faculty' => $companyData['faculty'],
                'institute' => $companyData['institute'],
                'leader_name' => $companyData['leader_name'],
                'email' => strtolower(str_replace([' ', '.'], ['_', ''], explode(' Dr. ', $companyData['leader_name'])[1])).'@university.edu',
                'phone' => '+49-'.rand(100, 999).'-'.rand(100000, 999999),
                'office_address' => 'Building '.rand(1, 20).', Room '.rand(100, 500).', University Campus',
                'website' => 'https://'.Str::slug($companyData['name']).'.university.edu',
                'orcid' => rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'research_keywords' => $companyData['research_keywords'],
                'research_description' => 'Our research group focuses on cutting-edge research in '.$companyData['research_keywords'].'. We employ state-of-the-art techniques and collaborate with leading institutions worldwide.',
                'funding_sources' => 'Deutsche Forschungsgemeinschaft (DFG), European Union (Horizon 2020), NFDI',
                'preferred_language' => 'english_german',
                'uses_eln' => true,
                'eln_system' => 'chemotion',
            ]);

            // Attach super_admin to the company
            $company->users()->attach($superAdmin, ['role' => 'admin']);

            $createdCompanies[] = $company;
            $this->command->info("Created company: {$company->name}");
        }

        // Get necessary data for samples
        $devices = Device::all();
        $solvents = Solvent::where('active', true)->get();
        $spectrumTypes = SpectrumType::all();

        if ($devices->isEmpty()) {
            $this->command->error('No devices found! Please run DeviceSeeder first.');

            return;
        }

        if ($solvents->isEmpty()) {
            $this->command->error('No solvents found! Please run SolventSeeder first.');

            return;
        }

        if ($spectrumTypes->isEmpty()) {
            $this->command->error('No spectrum types found! Please run SpectrumTypeSeeder first.');

            return;
        }

        $this->command->info('Creating molecules...');

        // Create some molecules
        $molecules = [
            [
                'name' => 'Caffeine',
                'identifier' => 'CAF001',
                'canonical_smiles' => 'CN1C=NC2=C1C(=O)N(C(=O)N2C)C',
                'standard_inchi' => 'InChI=1S/C8H10N4O2/c1-10-4-9-6-5(10)7(13)12(3)8(14)11(6)2/h4H,1-3H3',
                'molecular_formula' => 'C8H10N4O2',
                'molecular_weight' => 194.19,
            ],
            [
                'name' => 'Aspirin',
                'identifier' => 'ASP001',
                'canonical_smiles' => 'CC(=O)Oc1ccccc1C(=O)O',
                'standard_inchi' => 'InChI=1S/C9H8O4/c1-6(10)13-8-5-3-2-4-7(8)9(11)12/h2-5H,1H3,(H,11,12)',
                'molecular_formula' => 'C9H8O4',
                'molecular_weight' => 180.16,
            ],
            [
                'name' => 'Ethanol',
                'identifier' => 'ETH001',
                'canonical_smiles' => 'CCO',
                'standard_inchi' => 'InChI=1S/C2H6O/c1-2-3/h3H,2H2,1H3',
                'molecular_formula' => 'C2H6O',
                'molecular_weight' => 46.07,
            ],
            [
                'name' => 'Glucose',
                'identifier' => 'GLU001',
                'canonical_smiles' => 'C(C1C(C(C(C(O1)O)O)O)O)O',
                'standard_inchi' => 'InChI=1S/C6H12O6/c7-1-2-3(8)4(9)5(10)6(11)12-2/h2-11H,1H2',
                'molecular_formula' => 'C6H12O6',
                'molecular_weight' => 180.16,
            ],
            [
                'name' => 'Acetone',
                'identifier' => 'ACE001',
                'canonical_smiles' => 'CC(=O)C',
                'standard_inchi' => 'InChI=1S/C3H6O/c1-3(2)4/h1-2H3',
                'molecular_formula' => 'C3H6O',
                'molecular_weight' => 58.08,
            ],
        ];

        $createdMolecules = [];
        foreach ($molecules as $moleculeData) {
            $formula = $moleculeData['molecular_formula'];
            $weight = $moleculeData['molecular_weight'];
            unset($moleculeData['molecular_formula'], $moleculeData['molecular_weight']);

            $molecule = Molecule::create([
                'name' => $moleculeData['name'],
                'identifier' => $moleculeData['identifier'],
                'canonical_smiles' => $moleculeData['canonical_smiles'],
                'standard_inchi' => $moleculeData['standard_inchi'],
                'active' => true,
                'status' => 'APPROVED',
            ]);

            // Create properties for the molecule
            $molecule->properties()->create([
                'molecular_formula' => $formula,
                'molecular_weight' => $weight,
            ]);

            $createdMolecules[] = $molecule;
        }

        $this->command->info('Creating samples and processing them...');

        $statuses = ['submitted', 'approved', 'completed', 'completed', 'completed']; // More completed samples
        $priorities = ['LOW', 'MEDIUM', 'HIGH'];

        $sampleCount = 0;

        // Create samples for each company
        foreach ($createdCompanies as $company) {
            // Create 10-15 samples per company
            $numSamples = rand(10, 15);

            for ($i = 0; $i < $numSamples; $i++) {
                $device = $devices->random();
                $solvent = $solvents->random();
                $molecule = $createdMolecules[array_rand($createdMolecules)];
                $status = $statuses[array_rand($statuses)];
                $priority = $priorities[array_rand($priorities)];

                $sample = Sample::create([
                    'device_id' => $device->id,
                    'company_id' => $company->id,
                    'user_id' => $superAdmin->id,
                    'reference' => 'NMR-'.date('Ym').'-'.strtoupper(Str::random(2)).'-'.str_pad($sampleCount + 1, 4, '0', STR_PAD_LEFT),
                    'personal_key' => strtoupper(Str::random(8)),
                    'solvent_id' => $solvent->id,
                    'molecule_id' => $molecule->id,
                    'other_nuclei' => rand(0, 1) ? null : '13C, 15N',
                    'automation' => rand(0, 1) ? true : false,
                    'instructions' => rand(0, 1) ? 'Handle with care. Temperature sensitive.' : null,
                    'priority' => $priority,
                    'operator_id' => $superAdmin->id,
                    'status' => $status,
                    'comments' => $status === 'completed' ? 'Sample processed successfully.' : null,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now()->subDays(rand(0, 15)),
                ]);

                // Attach spectrum types to the sample
                $numSpectrumTypes = rand(1, 3);
                $sample->spectrumTypes()->attach(
                    $spectrumTypes->random($numSpectrumTypes)->pluck('id')
                );

                $sampleCount++;
            }

            $this->command->info("Created {$numSamples} samples for {$company->name}");
        }

        $this->command->info('');
        $this->command->alert('Company and Sample Seeding Complete!');
        $this->command->line('Created 1 personal company');
        $this->command->line('Created '.count($createdCompanies).' research group companies');
        $this->command->line("Created {$sampleCount} samples across all research groups");
        $this->command->line("Super admin ({$superAdmin->email}) is owner and member of all companies");
        $this->command->line('');
        $this->command->info('Sample statuses:');
        foreach ($statuses as $status) {
            $count = Sample::where('status', $status)->count();
            $this->command->line("  - {$status}: {$count} samples");
        }
    }
}
