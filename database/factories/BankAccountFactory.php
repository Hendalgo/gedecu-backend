<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\Store;
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
        $accountType = $this->faker->randomElement([1, 2]);
        $userId = null;
        $storeId = null;

        if ($accountType == 1) {
            $userId = User::factory()->create(['role_id' => 2])->id;
        } else {
            $storeId = Store::factory()->create()->id;
        }

        return [
            'name' => $this->faker->name,
            'identifier' => $this->faker->unique()->bankAccountNumber,
            'bank_id' => Bank::all()->random()->id,
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'account_type_id' => $accountType,
            'meta_data' => json_encode([]),
            'currency_id' => Currency::all()->random()->id,
            'user_id' => $userId,
            'store_id' => $storeId,
        ];
    }
}
