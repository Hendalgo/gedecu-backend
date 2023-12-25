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
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
            $table->dropColumn('inconsistence_check');
            $table->dropColumn('duplicated');
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
            $table->dropColumn('duplicated_status');
            $table->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('payment_reference')->nullable();
            $table->boolean('inconsistence_check')->nullable();
            $table->boolean('duplicated');
            $table->string('duplicated_status')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('bank_account_id');
            $table->foreign('bank_account_id')->references('id')->on('banks_accounts');
        });
    }
};
