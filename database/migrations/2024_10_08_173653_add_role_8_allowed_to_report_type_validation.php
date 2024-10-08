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
        DB::table('report_type_validations')
        ->where('name', 'user_id')
        ->where('report_type_id', 26)
        ->update([
            'validation' => 'required|exists:users,id|user_role:2;8'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('report_type_validations')
        ->where('name', 'user_id')
        ->where('report_type_id', 26)
        ->update([
            'validation' => 'required|exists:users,id|user_role:2'
        ]);
    }
};
