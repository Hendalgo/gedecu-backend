<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new Role();

        $user->id = 1;
        $user->name = 'Administrador';
        $user->description = 'Super admin, acceso a todas las funcionalidades de la aplicación';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#052C65","borderColor":"#9EC5FE","backgroundColor":"#E7F1FF"}}';

        $user->save();
        
        $user = new Role();

        $user->id = 2;
        $user->name = 'Gestor';
        $user->description = '';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#055160","borderColor":"#9EEAF9","backgroundColor":"#CFF4FC"}}';

        $user->save();

        $user = new Role();

        $user->id = 3;
        $user->name = 'Encargado de local';
        $user->description = '';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#290661","borderColor":"#C29FFA","backgroundColor":"#E0CFFC"}}';

        $user->save();

        $user = new Role();

        $user->id = 4;
        $user->name = 'Proveedor';
        $user->description = '';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#561435","borderColor":"#EFADCE","backgroundColor":"#F7D6E6"}}';

        $user->save();

        $user = new Role();

        $user->id = 5;
        $user->name = 'Depositante';
        $user->description = '';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#653208","borderColor":"#FECBA1","backgroundColor":"#FFE5D0"}}';

        $user->save();

        $user = new Role();

        $user->id = 6;
        $user->name = 'Caja Fuerte';
        $user->description = '';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#0A3622","borderColor":"#A3CFBB","backgroundColor":"#D1E7DD"}}';

        $user->save();

        $user = new Role();

        $user->id = 7;
        $user->name = 'Jefe';
        $user->description = 'Jefe de tienda';
        $user->permissions = json_encode([]);
        $user->config = '{"styles":{"color":"#58151C","borderColor":"#F1AEB5","backgroundColor":"#F8D7DA"}}';

        $user->save();
    }
}
