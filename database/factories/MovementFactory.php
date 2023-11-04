<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movement>
 */
class MovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rand = Report::inRandomOrder()->with('type')->first();
        $bank = BankAccount::find(json_decode($rand->meta_data)->bank_income);
        if ($rand->type->type === 'income') {
            $bank->balance = $bank->balance - abs($rand->amount);
        }
        elseif($rand->type->type === 'expense'){
            $bank->balance = $bank->balance + abs($rand->amount);
        }
        return [
            'amount' => $rand->amount,
            'bank_account_amount' => $bank->balance,
            'type' => $rand->type->type,
            'report_id' => $rand->id,
            'bank_account_id' => json_decode($rand->meta_data)->bank_income,
            'created_at' => $rand->created_at
        ];
    }
}
