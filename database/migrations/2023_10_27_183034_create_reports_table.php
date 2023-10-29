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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            /* 
                $table->float('rate')->nullable();
                $table->string('received_from')->nullable(); 
            */
            $table->string('payment_reference')->nullable();
            $table->boolean('inconsistence_check')->nullable();
            $table->boolean('duplicated');
            $table->string('duplicated_status')->nullable();
            $table->text('notes')->nullable();
        
            $table->json('meta_data');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('reports_types');
            
            $table->unsignedBigInteger('bank_account_id');
            $table->foreign('bank_account_id')->references('id')->on('banks_accounts');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
