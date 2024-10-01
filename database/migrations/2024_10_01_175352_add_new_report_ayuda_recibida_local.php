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
                'id' => 58,
                'name' => 'Ayuda Recibida de Local',
                'description' => 'Reporte de ayuda recibida de local',
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
                    'name' => 'Ayuda Recibida Local',
                    'type' => 2
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::table('roles_reports_permissions')->insert([
                [
                    "role_id" => 8,
                    "report_type_id" => 58,
                    "created_at" => now(),
                    "updated_at" => now()
                ]
            ]);
            DB::table('report_type_validations')->insert([
                [
                    'name' => 'store_id',
                    'validation' => 'required|exists:stores,id',
                    'validation_role' => 'all',
                    'report_type_id' => 58,
                ],
                [
                    'name' => 'amount',
                    'validation' => 'required|numeric',
                    'validation_role' => 'all',
                    'report_type_id' => 58,
                ],
                [
                    'name' => 'isDuplicated',
                    'validation' => 'required|boolean|is_false',
                    'validation_role' => 'all',
                    'report_type_id' => 58,
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
