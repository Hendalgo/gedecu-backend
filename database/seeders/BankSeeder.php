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
        $venezuela->country_id = 2;
        $venezuela->meta_data = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '#FDFDFD'
            ]
            ]);
        $venezuela->save();

        $santander = new Bank();

        $santander->name = 'Efectivo Colombia';
        $santander->country_id = 1;
        $santander->meta_data = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '#FDFDFD'
            ]
            ]);
        $santander->save();
    }
}
