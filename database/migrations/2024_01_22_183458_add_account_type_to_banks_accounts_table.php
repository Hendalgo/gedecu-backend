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
        Schema::table('banks_accounts', function (Blueprint $table) {
            $table->foreignId('account_type_id')->after('currency_id')->nullable()->constrained('accounts_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks_accounts', function (Blueprint $table) {
            $table->dropForeign(['account_type_id']);
            $table->dropColumn('account_type_id');
        });
    }
};
