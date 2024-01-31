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
        $user = new Role();
        $user->name = "Administrador";
        $user->description = "Super admin, acceso a todas las funcionalidades de la aplicación";
        $user->permissions = json_encode(array());

        $user->save();

        $user = new Role();
        $user->name = 'Gestor';
        $user->description = '';
        $user->permissions = json_encode(array());

        $user->save();

        $user = new Role();
        $user->name = 'Encargado de local';
        $user->description = '';
        $user->permissions = json_encode([]);
        
        $user->save();

        $user = new Role();
        $user->name = 'Proveedor';
        $user->description = '';
        $user->permissions = json_encode([]);
        
        $user->save();

        $user = new Role();
        $user->name = 'Depositante';
        $user->description = '';
        $user->permissions = json_encode([]);

        $user->save();
        
        $user = new Role();
        $user->name = 'Caja Fuerte';
        $user->description = '';
        $user->permissions = json_encode([]);

        $user->save();

        $user = new Role();
        $user->name = 'Jefe';
        $user->description = 'Jefe de tienda';
        $user->permissions = json_encode([]);

        $user->save();
    }
}
