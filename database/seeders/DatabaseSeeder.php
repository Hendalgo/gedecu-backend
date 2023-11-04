<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Movement;
use App\Models\Report;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(RolesSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(ReportsTypesSeeder::class);
        $this->call(BankSeeder::class);
        User::factory(50)->create();
        Store::factory(20)->create();
        $this->call(BankAccountSeeder::class);
        Report::factory(100)->create();
        Movement::factory(100)->create();
        /* $this->call(UserSeeder::class); */
    }
}
