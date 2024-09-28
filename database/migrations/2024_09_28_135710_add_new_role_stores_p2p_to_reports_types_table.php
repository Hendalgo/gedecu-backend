<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('reports_types')->insert([
            'id' => 57,
            'name' => 'Ayuda Billetera P2P',
            'description' => 'Reporte de ayuda a la billetera p2p del local',
            'type' => 'expense',
            'config' => json_encode([
                'styles' => [
                    'color' => '#052C65',
                    'borderColor' => '#9EC5FE',
                    'backgroundColor' => '#E7F1FF',
                ]
            ]),
            'country' => 1,
            'meta_data' => json_encode([
                'name' => 'Billetera P2P',
                'type' => 2
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reports_types')->where('id', 57)->delete();
    }
};
