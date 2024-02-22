<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Bank;
use App\Models\BankAccount;
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
        $this->call(CountriesSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AccountTypeSeeder::class);
        $this->call(ReportsTypesSeeder::class);
        $this->call(ReportTypeValidationsSeeder::class);
        $this->call(RoleReportPermissionSeeder::class);
        Bank::factory(20)->create();
        User::factory(50)->create();
        Store::factory(30)->create();
        BankAccount::factory(10)->create();
        /*  */
    }
}
