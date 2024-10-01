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
        DB::table('report_type_validations')->insert([
            [
                'name' => 'account_id',
                'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
                'validation_role' => 'all',
                'report_type_id' => 57,
            ],
            [
                'name' => 'amount',
                'validation' => 'required|numeric',
                'validation_role' => 'all',
                'report_type_id' => 57,
            ],
            [
                'name'=> 'user_id',
                'validation' => 'required|exists:users,id|user_role:8',
                'validation_role' => 'all',
                'report_type_id' => 57,
            ],
            [
                'name' => 'isDuplicated',
                'validation' => 'required|boolean|is_false',
                'validation_role' => 'all',
                'report_type_id' => 57,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('report_type_validations')->where('report_type_id', 57)->delete();
    }
};
