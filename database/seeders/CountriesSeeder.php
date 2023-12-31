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
        $colombia->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD'
            ]
            ]);
        
        $colombia->save();


        $venezuela = new Country();

        $venezuela->name = 'Venezuela';
        $venezuela->img = '';
        $venezuela->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD'
            ]
            ]);
        $venezuela->shortcode = 'VE';

        $venezuela->save();
    }
}
