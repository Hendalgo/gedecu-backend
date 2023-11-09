<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\BankAccount;
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
        $bankAccount= BankAccount::inRandomOrder()->first();
        $bank = Bank::inRandomOrder()->first();
        $type =  ReportType::inRandomOrder()->first();
        $amount = fake()->randomFloat(2, 5000000, 1000000000000);
        if ($type->type === 'income') {
            $bankAccount->balance = $bankAccount->balance + abs($amount);
            $bankAccount->save();
        }
        elseif($type->type === 'expense'){
            $bankAccount->balance = $bankAccount->balance - abs($amount);
            $bankAccount->save();
        }

        return [
            'amount' => $amount,
            'duplicated' => fake()->boolean(),
            'payment_reference' => fake()->sentence(),
            'duplicated_status' => fake()->randomElement(['done', 'cancel']),
            'meta_data' => json_encode([
                'rate' => fake()->optional()->randomFloat(2, 5, 100),
                'petition_bank' => $bank->id,
                'store' => Store::inRandomOrder()->first()->id,
            ]),
            'inconsistence_check' => fake()->boolean(),
            'notes' => fake()->paragraph(),
            'user_id' => User::inRandomOrder()->first()->id,
            'type_id' => $type->id,
            'bank_account_id' =>  $bankAccount->id,
            'created_at' => fake()->dateTimeBetween('-7 days', 'now')
        ];
    }
}
