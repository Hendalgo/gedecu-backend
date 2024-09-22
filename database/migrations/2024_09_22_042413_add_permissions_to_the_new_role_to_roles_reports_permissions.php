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
        // Verifica que los report_type_id existen en la tabla reports_types
        $reportTypeIds = DB::table('reports_types')->whereIn('id', [47, 48, 49, 50, 51, 52, 53, 54, 55, 56])->pluck('id')->toArray();
        
        // Inserta los permisos solo si todos los report_type_id existen
        if (count($reportTypeIds) === 10) {
            DB::table('roles_reports_permissions')->insert([
                [
                    "role_id" => 8,
                    "report_type_id" => 47,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 48,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 49,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 50,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 51,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 52,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 53,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 54,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 55,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
                [
                    "role_id" => 8,
                    "report_type_id" => 56,
                    "created_at" => now(),
                    "updated_at" => now()
                ],
            ]);
        } else {
            throw new Exception('Some report_type_id do not exist in reports_types table.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles_reports_permissions')->whereIn('role_id', [8])->delete();
    }
};