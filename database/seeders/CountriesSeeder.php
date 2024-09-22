<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colombia = new Country();
        $colombia->id = 1;
        $colombia->name = 'Colombia';
        $colombia->img = '';
        $colombia->shortcode = 'CO';
        $colombia->locale = 'es_CO';
        $colombia->is_initial = true;
        $colombia->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD',
            ],
        ]);

        $colombia->save();

        $venezuela = new Country();

        $venezuela->id = 2;
        $venezuela->name = 'Venezuela';
        $venezuela->img = '';
        $venezuela->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD',
            ],
        ]);
        $venezuela->is_initial = true;
        $venezuela->locale = 'es_VE';
        $venezuela->shortcode = 'VE';

        $venezuela->save();

        $usa = new Country();

        $usa->id = 3;
        $usa->name = 'Estados Unidos';
        $usa->img = '';
        $usa->config = json_encode([
            'styles' => [
                'color' => '#FFFFFF',
                'backgroundColor' => '#000000',
                'borderColor' => '$FDFDFD',
            ],
        ]);
        $usa->is_initial = false;
        $usa->locale = 'en_US';
        $usa->shortcode = 'US';
        $usa->save();
    }
}
