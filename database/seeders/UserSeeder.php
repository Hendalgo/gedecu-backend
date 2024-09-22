<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Super administrador',
            'email' => 'admin@gedecu.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'country_id' => Country::inRandomOrder()->first()->id,
            'role_id' => 1,
            'permissions' => json_encode([
                'users' => true,
                'roles' => true,
                'allowed_banks' => true,
                'allowed_currencies' => true,
                'accounts' => true,
                'transactions' => true,
                'reports' => true,
                'settings' => true,
            ]),
            'is_initial' => true,
        ]);

        User::create([
            'name' => 'Pedro Sola',
            'email' => 'pedro@gedecu.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi85',
            'country_id' => Country::inRandomOrder()->first()->id,
            'role_id' => 7,
            'permissions' => json_encode([
                'users' => true,
                'roles' => true,
                'allowed_banks' => true,
                'allowed_currencies' => true,
                'accounts' => true,
                'transactions' => true,
                'reports' => true,
                'settings' => true,
            ]),
            'is_initial' => true,
        ]);

        User::create([
            'name' => 'Juan Escutia',
            'email' => 'juan@gedecu.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi85',
            'country_id' => Country::inRandomOrder()->first()->id,
            'role_id' => 7,
            'permissions' => json_encode([
                'users' => true,
                'roles' => true,
                'allowed_banks' => true,
                'allowed_currencies' => true,
                'accounts' => true,
                'transactions' => true,
                'reports' => true,
                'settings' => true,
            ]),
            'is_initial' => true,
        ]);
    }
}
