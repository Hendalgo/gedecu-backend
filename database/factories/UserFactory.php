<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $email = $this->faker->unique()->safeEmail();
        $email_verified_at = now();
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password
        $remember_token = Str::random(10);
        $country_id = Country::where('delete', false)->inRandomOrder()->first()->id;
        $role_id = null;
        $delete = $this->faker->boolean();
        if ($country_id === 2){
            $role_id = Role::inRandomOrder()->whereIn('id', [1, 2])->first()->id;
        }
        else{
            $role_id = Role::inRandomOrder()->where('id', "!=", 2)->first()->id;
        }
        return [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $email_verified_at,
            'password' => $password,
            'remember_token' => $remember_token,
            'country_id' => $country_id,
            'role_id' => $role_id,
            'delete' => $delete,
        ];
    }
}
