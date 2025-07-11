<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $groupName = fake()->randomElement([
            'Computational Chemistry Research Group',
            'Analytical Chemistry Laboratory',
            'Organic Synthesis Research Group',
            'Materials Science Laboratory',
            'Biochemistry Research Group',
            'Physical Chemistry Laboratory',
            'Environmental Chemistry Group',
            'Pharmaceutical Research Laboratory',
            'Nanotechnology Research Group',
            'Catalysis Research Laboratory',
        ]);

        $faculties = [
            'Faculty of Chemistry and Earth Sciences',
            'Faculty of Natural Sciences',
            'Faculty of Science and Technology',
            'Faculty of Chemistry',
            'Faculty of Chemical Engineering',
        ];
        $institutes = [
            'Institute for Inorganic and Analytical Chemistry',
            'Institute of Organic Chemistry',
            'Institute of Physical Chemistry',
            'Institute of Materials Science',
            'Institute of Chemical Biology',
        ];

        $elnSystems = ['chemotion', 'elabftw', 'rspace', 'benchling', 'labarchives', 'labguru'];
        $usesEln = fake()->boolean(70); // 70% chance of using ELN

        return [
            'name' => $groupName,
            'description' => fake()->paragraph(2),
            'slug' => Str::slug($groupName),
            'search_slug' => Str::slug($groupName),
            'personal_company' => false,
            'reference' => fake()->unique()->regexify('[A-Z]{3}[0-9]{4}'),

            // Faculty & Institute
            'faculty' => fake()->randomElement($faculties),
            'institute' => fake()->randomElement($institutes),

            // Group Leader
            'leader_name' => 'Prof. Dr. '.fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'office_address' => fake()->streetAddress().', Room '.fake()->numberBetween(100, 400).', '.fake()->postcode().' '.fake()->city().', Germany',
            'website' => fake()->url(),
            'orcid' => fake()->regexify('[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}'),

            // Research Focus
            'research_keywords' => fake()->randomElement([
                'Organic Synthesis, Catalysis, Natural Products, Drug Discovery',
                'Computational Chemistry, Molecular Modeling, Quantum Chemistry, Machine Learning',
                'Analytical Chemistry, Mass Spectrometry, NMR Spectroscopy, Chromatography',
                'Materials Science, Nanotechnology, Surface Chemistry, Electrochemistry',
                'Biochemistry, Enzymology, Protein Chemistry, Chemical Biology',
            ]),
            'research_description' => fake()->paragraph(4),
            'funding_sources' => fake()->randomElement([
                'Deutsche Forschungsgemeinschaft (DFG), European Union (Horizon 2020)',
                'National Science Foundation (NSF), National Institutes of Health (NIH)',
                'Volkswagen Foundation, Alexander von Humboldt Foundation',
                'BMBF, Max Planck Society, Helmholtz Association',
            ]),
            'preferred_language' => fake()->randomElement(['english', 'german', 'english_german']),

            // ELN Information
            'uses_eln' => $usesEln,
            'eln_system' => $usesEln ? fake()->randomElement($elnSystems) : null,
            'eln_other' => null, // Will be set only if eln_system is 'other'
        ];
    }
}
