<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peso = new Currency();

        $peso->name = 'Peso Colombiano';
        $peso->shortcode = 'COP';
        $peso->symbol = '$';
        $peso->country_id = 1;
        $peso->save();

        $bolivar = new Currency();

        $bolivar->name = "Bolivar Digital";
        $bolivar->shortcode = 'VES';
        $bolivar->symbol = 'Bs.';
        $bolivar->country_id = 2;
        $bolivar->save();
    }
}
