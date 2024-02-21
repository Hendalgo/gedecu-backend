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
        Schema::create('report_type_validations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('validation');
            $table->string('validation_role')->default('all');
            $table->foreignId('report_type_id')->constrained('reports_types')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_type_validations');
    }
};
