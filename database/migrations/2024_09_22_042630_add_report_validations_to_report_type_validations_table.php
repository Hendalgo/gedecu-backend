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
        DB::table("report_type_validations")->insert([
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 47,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 47,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            /** Proveedor */
            [
                "name"=> "supplier_id",
                "validation" => "required|exists:users,id|user_role:supplier",
                "validation_role" => "all",
                "report_type_id" => 48,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 48,
            ],
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 48,
            ],
            [
                "name" => "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 48,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            /** Ayuda Recibida */
            [
                "name"=> "user_id",
                "validation" => "required|exists:users,id|user_role:8",
                "validation_role" => "all",
                "report_type_id" => 49,
            ],
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 49,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 49,
            ],
            [
                "name"=> "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 49,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            /** Ayuda Efectivo */
            [
                "name"=> "store_id",
                "validation" => "required|exists:store,id",
                "validation_role" => "all",
                "report_type_id" => 50,
            ],
            [
                "name" => "account_id",
                "validation" => "required|exists:banks_banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 50,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 50,
            ],
            [
                "name"=> "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 50,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            /** Traspaso */
            [
                "name"=> "senderAccount_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            [
                "name"=> "receiverAccount_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            [
                "name"=> "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 51,
            ],
            /** Devolucion */
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 52,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 52,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 52,
            ],
            /** Billetera Egreso */
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 53,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 53,
            ],
            [
                "name"=> "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 53,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 53,
            ],
            /** Ayuda Realizada */
            [
                "name"=> "user_id",
                "validation" => "required|exists:users,id|user_role:8",
                "validation_role" => "all",
                "report_type_id" => 54,
            ],
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 54,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 54,
            ],
            [
                "name"=> "reference",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 54,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 54,
            ],
            /** Comisiones */
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 55,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 55,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 55,
            ],
            /** Otros */
            [
                "name"=> "account_id",
                "validation" => "required|exists:banks_accounts,id|bank_account_owner",
                "validation_role" => "all",
                "report_type_id" => 56,
            ],
            [
                "name"=> "amount",
                "validation" => "required|numeric",
                "validation_role" => "all",
                "report_type_id" => 56,
            ],
            [
                "name"=> "motive",
                "validation" => "required",
                "validation_role" => "all",
                "report_type_id" => 56,
            ],
            [
                "name" => "is_duplicated",
                "validation" => "required|boolean|is_false",
                "validation_role" => "all",
                "report_type_id" => 56,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table("report_type_validations")->whereIn("report_type_id", [47, 48, 49, 50, 51, 52, 53, 54, 55, 56])->delete();
    }
};