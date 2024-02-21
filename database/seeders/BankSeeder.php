<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venezuela = new Bank();

        $venezuela->name = 'Bancolombia';
        $venezuela->country_id = 1;
        $venezuela->meta_data = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '#FDFDFD',
            ],
        ]);

        $venezuela->currency_id = 1;
        $venezuela->save();

        $santander = new Bank();

        $santander->name = 'Banco de venezuela';
        $santander->country_id = 2;
        $santander->meta_data = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '#FDFDFD',
            ],
        ]);
        $santander->currency_id = 2;
        $santander->save();
    }
}
