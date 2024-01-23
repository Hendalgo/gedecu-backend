<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $balance = $this->faker->numberBetween(1000, 1000000);
        $identifier = $this->faker->creditCardNumber();
        $bank_id = Bank::where('delete', false)->inRandomOrder()->first()->id;
        $user_id = User::where('delete', false)->whereIn('role_id', [2])->inRandomOrder()->first()->id;
        $currecy_id = $this->faker->numberBetween(1, 2, 3);
        $delete = $this->faker->boolean();
        return [
            'name' => $name,
            'balance' => $balance,
            'identifier' => $identifier,
            'bank_id' => $bank_id,
            'user_id' => $user_id,
            'currency_id' => $currecy_id,
            'delete' => $delete,
        ];
    }
}
