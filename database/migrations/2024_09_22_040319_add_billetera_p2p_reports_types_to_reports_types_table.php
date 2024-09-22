<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('reports_types')->insert([
            [
                'id' => 47,
                'name' => 'Por Billetera P2P',
                'description' => 'Reporte de la Billetera P2P',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#052C65',
                        'borderColor' => '#9EC5FE',
                        'backgroundColor' => '#E7F1FF',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'type' => 'billetera_p2p',
                    'name' => 'Billetera P2P',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 48,
                'name' => 'Por Proveedor',
                'description' => 'Reporte de la Billetera P2P por proveedor',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#052C65',
                        'borderColor' => '#9EC5FE',
                        'backgroundColor' => '#E7F1FF',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'type' => 'billetera_p2p',
                    'name' => 'Proveedor',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 49,
                'name' => 'Ayuda Recibida',
                'description' => 'Reporte de la Billetera P2P por ayuda recibida',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#052C65',
                        'borderColor' => '#9EC5FE',
                        'backgroundColor' => '#E7F1FF',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'type' => 'billetera_p2p',
                    'name' => 'Ayuda Recibida',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 50,
                'name' => 'Ayuda Recibida (Efectivo)',
                'description' => 'Reporte de la Billetera P2P por ayuda recibida en efectivo',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#052C65',
                        'borderColor' => '#9EC5FE',
                        'backgroundColor' => '#E7F1FF',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'type' => 'billetera_p2p',
                    'name' => 'Ayuda Recibida (Efectivo)',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 51,
                'name' => 'Traspasos',
                'description' => 'Reporte de la Billetera P2P por traspasos',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Traspasos entre billeteras',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => '2023-11-09 06:21:08',
                'updated_at' => '2023-11-09 06:21:08'
            ],
            [
                'id' => 52,
                'name' => 'Devoluciones',
                'description' => 'Reporte de la Billetera P2P por devoluciones',
                'type' => 'income',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Devoluciones',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 53,
                'name' => 'Billetera P2P',
                'description' => 'Reporte de la Billetera P2P Por egresos',
                'type' => 'expense',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Billetera P2P',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 54,
                'name' => 'Ayuda Realizada',
                'description' => 'Reporte de la Billetera P2P por ayuda realizada',
                'type' => 'expense',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Ayuda Realizada',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 55,
                'name' => 'Comisiones',
                'description' => 'Ingreso de billeter',
                'type' => 'expense',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Comisiones',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => '2023-11-09 21:44:33',
                'updated_at' => '2023-11-10 00:48:52'
            ],
            [
                'id' => 56,
                'name' => 'Otros',
                'description' => 'Reporte de la Billetera P2P por otros',
                'type' => 'expense',
                'config' => json_encode([
                    'styles' => [
                        'color' => '#58151C',
                        'borderColor' => '#F1AEB5',
                        'backgroundColor' => '#F8D7DA',
                    ]
                ]),
                'country' => 0,
                'meta_data' => json_encode([
                    'name' => 'Otros',
                    'type' => 'billetera_p2p',
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reports_types')->whereIn('id', [47, 48, 49, 50, 51, 52, 53, 54, 55, 56])->delete();
    }
};