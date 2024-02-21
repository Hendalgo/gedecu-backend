<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $AccountType = new AccountType();
        $AccountType->name = 'Banco';
        $AccountType->description = 'Cuenta bancaria';

        $AccountType->save();

        $AccountType = new AccountType();
        $AccountType->name = 'Wallet';
        $AccountType->description = 'Cuenta de billetera';

        $AccountType->save();

        $AccountType = new AccountType();
        $AccountType->name = 'Efectivo';
        $AccountType->description = 'Cuenta de efectivo de los locales';

        $AccountType->save();
    }
}
