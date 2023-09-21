<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = new Role();
        $admin->name = "administrador";
        $admin->description = "Super admin, acceso a todas las funcionalidades de la aplicación";
        $admin->permissions = json_encode([]);

        $admin->save();

        $gestor = new Role();
        $gestor->name = 'gestor';
        $gestor->description = '';
        $gestor->permissions = json_encode([]);

        $gestor->save();

        $depositante = new Role();
        $depositante->name = 'depositante';
        $depositante->description = '';
        $depositante->permissions = json_encode([]);
        
        $depositante->save();

        $corresponsal = new Role();
        $corresponsal->name = 'corresponsal';
        $corresponsal->description = '';
        $corresponsal->permissions = json_encode([]);
        
        $corresponsal->save();

        $caja = new Role();
        $caja->name = 'caja fuerte';
        $caja->description = '';
        $caja->permissions = json_encode([]);

        $caja->save();
        
        $encargado = new Role();
        $encargado->name = 'encargado de tienda';
        $encargado->description = '';
        $encargado->permissions = json_encode([]);

        $encargado->save();
    }
}
