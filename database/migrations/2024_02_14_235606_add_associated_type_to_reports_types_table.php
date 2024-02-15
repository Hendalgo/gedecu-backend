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
        Schema::table('reports_types', function (Blueprint $table) {
            $table->foreignId('associated_type_id')->after('config')->nullable()->constrained('reports_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('associated_type_id');
        });
    }
};
