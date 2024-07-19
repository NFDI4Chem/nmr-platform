<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Device;
use App\Models\Molecule;
use App\Models\Operator;
use App\Models\Sample;
use App\Models\Solvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class SampleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sample::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'company_id' => Company::factory(),
            'reference' => $this->faker->word(),
            'solvent_id' => Solvent::factory(),
            'molecule_id' => Molecule::factory(),
            'spectrum_type' => $this->faker->word(),
            'instructions' => $this->faker->text(),
            'additional_infofile_id' => $this->faker->word(),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'operator_id' => Operator::factory(),
        ];
    }
}
