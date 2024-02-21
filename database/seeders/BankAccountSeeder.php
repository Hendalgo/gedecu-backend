<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cash = new BankAccount();

        $cash->name = 'Propietario 1';
        $cash->balance = 0;
        $cash->bank_id = 1;
        $cash->identifier = 158481878718718718;
        $cash->meta_data = json_encode([]);
        $cash->save();

        $cash2 = new BankAccount();

        $cash2->name = 'Marta Sanchez';
        $cash2->balance = 0;
        $cash2->bank_id = 2;
        $cash2->identifier = 158481878718718718;
        $cash2->meta_data = json_encode([]);
        $cash2->save();
    }
}
