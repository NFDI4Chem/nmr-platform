<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Device;
use App\Models\Molecule;
use App\Models\Operator;
use App\Models\Sample;
use App\Models\Solvent;

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
            'identifier' => $this->faker->word(),
            'solvent_id' => Solvent::factory(),
            'molecule_id' => Molecule::factory(),
            'spectrum_type' => $this->faker->word(),
            'instructions' => $this->faker->text(),
            'featured_image_id' => $this->faker->word(),
            'priority' => $this->faker->randomElement(["high","medium","low"]),
            'operator_id' => Operator::factory(),
        ];
    }
}
