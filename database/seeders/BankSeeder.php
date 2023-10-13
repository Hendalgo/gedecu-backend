<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venezuela = new Bank();

        $venezuela->name = 'Efectivo venezuela';
        $venezuela->amount = 105458489485.25;
        $venezuela->country_id = 2;
        $venezuela->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD'
            ]
            ]);
        $venezuela->save();

        $santander = new Bank();

        $santander->name = 'Efectivo Colombia';
        $santander->amount = 5485181884.25;
        $santander->country_id = 1;
        $santander->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD'
            ]
            ]);
        $santander->save();
    }
}
