<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\ReportType;
use App\Models\Store;
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
            'duplicated' => fake()->boolean(),
            'payment_reference' => fake()->sentence(),
            'duplicated_status' => fake()->randomElement(['done', 'cancel']),
            'meta_data' => json_encode([
                'rate' => fake()->optional()->randomFloat(2, 5, 100),
                'bank_income' => Bank::inRandomOrder()->first()->id,
            ]),
            'inconsistence_check' => fake()->boolean(),
            'notes' => fake()->paragraph(),
            'user_id' => User::inRandomOrder()->first()->id,
            'type_id' => ReportType::inRandomOrder()->first()->id,
            'bank_id' => Bank::inRandomOrder()->first()->id,
            'store_id' => Store::inRandomOrder()->first()->id,
            'created_at' => fake()->date()
        ];
    }
}
