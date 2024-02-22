<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $img = $this->faker->imageUrl(640, 480, 'flags', true);
        $country_id = Country::where('delete', false)->inRandomOrder()->first()->id;
        $type_id = $this->faker->numberBetween(1, 2);
        $delete = $this->faker->boolean();

        return [
            'name' => $name,
            'img' => $img,
            'meta_data' => json_encode([
                'styles' => [],
            ]),
            'country_id' => $country_id,
            'type_id' => $type_id,
            'delete' => $delete,
        ];
    }
}
