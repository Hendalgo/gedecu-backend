<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colombia = new Country();

        $colombia->name = 'Colombia';
        $colombia->img = '';
        $colombia->shortcode = 'CO';
        $colombia->currency_id = 1;
        
        $colombia->save();


        $venezuela = new Country();

        $venezuela->name = 'Venezuela';
        $venezuela->img = '';
        $venezuela->shortcode = 'VE';
        $venezuela->currency_id = 2;

        $venezuela->save();
    }
}
