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
        Schema::table('banks', function (Blueprint $table) {
            $table->dropForeign(['currency_id']); // Elimina la restricción de clave foránea
            $table->dropColumn('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id'); // Agrega la columna

            $table->foreign('currency_id') // Establece la columna como clave foránea
                ->references('id')
                ->on('currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
