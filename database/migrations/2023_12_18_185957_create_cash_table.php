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
        Schema::create('cash', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->double('amount');
            $table->unsignedBigInteger('currency_id'); // Agrega la columna
            $table->foreign('currency_id') // Establece la columna como clave foránea
                ->references('id')
                ->on('currencies')
                ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->unsignedBigInteger('store_id'); // Agrega la columna
            $table->foreign('store_id') // Establece la columna como clave foránea
                ->references('id')
                ->on('stores')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash');
    }
};
