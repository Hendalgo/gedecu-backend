<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peso = new Currency();

        $peso->id = 1;
        $peso->name = 'Peso Colombiano';
        $peso->shortcode = 'COP';
        $peso->symbol = '$';
        $peso->is_initial = true;
        $peso->save();

        $bolivar = new Currency();

        $bolivar->id = 2;
        $bolivar->name = 'Bolivar Digital';
        $bolivar->shortcode = 'VES';
        $bolivar->symbol = 'Bs.';
        $bolivar->is_initial = true;
        $bolivar->save();

        $dolar = new Currency();

        $dolar->id = 3;
        $dolar->name = 'Dolar Americano';
        $dolar->shortcode = 'USD';
        $dolar->symbol = '$';
        $dolar->is_initial = true;
        $dolar->save();
    }
}
