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
            //
            $table->unsignedBigInteger('store_id')->nullable()->after('user_id');
            $table->foreign('store_id')->references('id')->on('stores')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks_accounts', function (Blueprint $table) {
            //
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
