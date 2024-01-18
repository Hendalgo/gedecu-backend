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
        Schema::table('subreports', function (Blueprint $table) {
            $table->boolean('duplicate_status')->nullable();
            $table->json('duplicate_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subreports', function (Blueprint $table) {
            $table->dropColumn('duplicate_status');
            $table->dropColumn('duplicate_data');
        });
    }
};