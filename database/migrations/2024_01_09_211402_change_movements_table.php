<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('bank_account_amount');
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
            $table->unsignedBigInteger('sub_report_id')->nullable()->after('report_id');
            $table->unsignedBigInteger('currency_id')->nullable()->after('sub_report_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropColumn('sub_report_id');
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('report_id');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts');
            $table->double('bank_account_amount', 10, 2)->after('bank_account_id');
        });
    }
};
