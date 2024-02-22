<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'), // password
            'country_id' => Country::all()->random()->id,
            'role_id' => Role::all()->random()->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if ($user->role_id == 5 || $user->role_id == 6) {
                $currency = Country::with('currency')->find($user->country_id);
                UserBalance::create([
                    'user_id' => $user->id,
                    'balance' => fake()->randomFloat(2, 0, 1000),
                    'currency_id' => $currency->currency->id,
                ]);
            }
        });
    }
}
