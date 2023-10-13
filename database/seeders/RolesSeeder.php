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
        $admin->permissions = json_encode(
            array(
                "banks" =>[
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ],
                "countries" => [
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ],
                "reports" =>[
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ],
                "currencies"=>[
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ],
                "roles"=>[
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ],
                "users"=>[
                    "read" => true, 
                    "create"=> true, 
                    "update"=> true, 
                    "delete" =>true
                ]
            )
        );

        $admin->save();

        $gestor = new Role();
        $gestor->name = 'gestor';
        $gestor->description = '';
        $gestor->permissions = json_encode([
            "banks" =>[
                "read" => true, 
                "create"=> false, 
                "update"=> false, 
                "delete" =>false
            ],
            "countries" => [
                "read" => true, 
                "create"=> false, 
                "update"=> false, 
                "delete" => false
            ],
            "reports" =>[
                "read" => true, 
                "create"=> true, 
                "update"=> false, 
                "delete" => false
            ],
            "currencies"=>[
                "read" => true, 
                "create"=> false, 
                "update"=> false, 
                "delete" => false
            ],
            "roles"=>[
                "read" => true, 
                "create"=> false, 
                "update"=> false, 
                "delete" => false
            ],
            "users"=>[
                "read" => false, 
                "create"=> false, 
                "update"=> false, 
                "delete" =>false
            ]
        ]);

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
