<?php

namespace Database\Factories;

use App\Models\Solvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Solvent::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'molecular_formula' => $this->faker->word(),
            'molecular_weight' => $this->faker->randomFloat(2, 0, 999999.99),
            'meta_data' => '{}',
        ];
    }
}
