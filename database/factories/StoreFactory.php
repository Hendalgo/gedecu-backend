<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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
    public function definition()
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('id', 3);
        })->get()->random();

        $country = Country::all()->random();

        $storeData = [
            'name' => $this->faker->company,
            'location' => $this->faker->address,
            'user_id' => $user->id,
            'country_id' => $country->id,
        ];

        DB::transaction(function () use ($storeData) {
            $store = Store::create($storeData);

            $bankAccountData = [
                'name' => 'Efectivo',
                'identifier' => 'Efectivo',
                'balance' => $this->faker->randomFloat(2, 0, 10000),
                'currency_id' => $store->country->currency->id,
                'account_type_id' => 3,
            ];

            $store->accounts()->create($bankAccountData);
        });

        return $storeData;
    }
}
