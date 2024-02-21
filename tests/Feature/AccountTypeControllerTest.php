<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AccountTypeControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_all_account_types(): void
    {
        $user = User::factory()->state(['role_id' => 1, 'delete' => false])->create();
        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('access_token');
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/banks/account-types');

        $response->assertOk();

        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'created_at',
                'updated_at',
            ],

        ]);
    }

    public function test_get_account_types_without_authentication(): void
    {
        $response = $this->get('/api/banks/account-types');

        $response->assertUnauthorized();
    }
}
