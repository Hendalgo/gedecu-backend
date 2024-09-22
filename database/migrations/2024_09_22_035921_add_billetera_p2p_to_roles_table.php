<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->insert([
            [
                'id'=> 8,
                'name'=> 'Billetera P2P',
                'description'=> 'Rol del la Billetera P2P',
                'permissions'=> json_encode([]),
                'created_at'=> now(),
                'updated_at'=> now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('id', 8)->delete();
    }
};
