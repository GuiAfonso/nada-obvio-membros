<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'categoria' => fake()->randomElement(['Embalagens', 'Logística', 'Gráfica']),
            'categoria_color' => fake()->randomElement(['blue', 'green', 'purple']),
            'cidade' => fake()->city(),
            'whatsapp' => fake()->numerify('##999#######'),
        ];
    }
}
