<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 500000),
            'rate' => fake()->optional()->randomFloat(2, 5, 100),
            'received_from' => fake()->name(),
            'payment_reference' => fake()->sentence(),
            'notes' => fake()->paragraph(),
            'user_id' => User::inRandomOrder()->first()->id,
            'type_id' => ReportType::inRandomOrder()->first()->id,
            'bank_id' => Bank::inRandomOrder()->first()->id
        ];
    }
}
