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
        Schema::table('inconsistences', function (Blueprint $table) {
            $table->foreignId('associated_id')->after('subreport_id')->cascadeOnDelete()->nullable()->constrained('subreports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inconsistences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('associated_id');
        });
    }
};
