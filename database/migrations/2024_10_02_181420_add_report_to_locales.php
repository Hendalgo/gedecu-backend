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
        DB::transaction(function () {
            DB::table('reports_types')->insert([
                [
                    'id' => 59,
                    'name' => 'Ayuda Realizada a P2P (Transferencia)',
                    'description' => 'Reporte de ayuda realizada a P2P (Transferencia)',
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
                        'name' => 'Ayuda Realizada a P2P (Transferencia)',
                        'type' => 2
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 60,
                    'name' => 'Ayuda Recibida de P2P (Transferencia)',
                    'description' => 'Reporte de ayuda recibida de P2P (Transferencia)',
                    'type' => 'neutro',
                    'config' => json_encode([
                        'styles' => [
                            'color' => '#052C65',
                            'borderColor' => '#9EC5FE',
                            'backgroundColor' => '#E7F1FF',
                        ]
                    ]),
                    'country' => 1,
                    'meta_data' => json_encode([
                        'name' => 'Ayuda Recibida de P2P (Transferencia)',
                        'type' => 0
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('roles_reports_permissions')->insert([
                [
                    "role_id" => 3,
                    "report_type_id" => 59,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 60,
                    "created_at" => now(),
                    "updated_at" => now()
                ]
            ]);
            DB::table('report_type_validations')->insert([
                [
                    'name' => 'account_id',
                    'validation' => 'required|exists:accounts,id|bank_account_owner',
                    'validation_role' => 'all',
                    'report_type_id' => 59,
                ],
                [
                    'name'=> 'user_id',
                    'validation' => 'required|exists:users,id|user_role:8',
                    'validation_role' => 'all',
                    'report_type_id' => 59,
                ],
                [
                    'name' => 'amount',
                    'validation' => 'required|numeric',
                    'validation_role' => 'all',
                    'report_type_id' => 59,
                ],
                [
                    'name' => 'isDuplicated',
                    'validation' => 'required|boolean|is_false',
                    'validation_role' => 'all',
                    'report_type_id' => 59,
                ],
                

                [
                    'name'=> 'user_id',
                    'validation' => 'exists:users,id',
                    'validation_role' => 'all',
                    'report_type_id' => 60,
                ],
                [
                    'name'=> 'store_id',
                    'validation' => 'exists:stores,id',
                    'validation_role' => 'all',
                    'report_type_id' => 60,
                ],
                [
                    'name' => 'amount',
                    'validation' => 'required|numeric',
                    'validation_role' => 'all',
                    'report_type_id' => 60,
                ],
                [
                    'name' => 'isDuplicated',
                    'validation' => 'required|boolean|is_false',
                    'validation_role' => 'all',
                    'report_type_id' => 60,
                ],
                [
                    'name' => 'currency_id',
                    'validation' => 'required|exists:currencies,id',
                    'validation_role' => 'all',
                    'report_type_id' => 60,
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
