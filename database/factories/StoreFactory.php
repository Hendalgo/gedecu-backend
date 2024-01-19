<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $location = $this->faker->address();
        $country = Country::where('delete', false)->where('id', '!=', 2)->inRandomOrder()->first()->id;
        $user = User::where('delete', false)->where('role_id', 3)->where('country_id', $country)->inRandomOrder()->first()->id;
        $delete = $this->faker->boolean();

        return [
            'name' => $name,
            'location' => $location,
            'country_id' => $country,
            'user_id' => $user,
            'delete' => $delete,
        ];
    } 
}
