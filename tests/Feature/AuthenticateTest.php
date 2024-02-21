<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_interacting_authentication(): void
    {
        $admin = User::factory()->state(['role_id' => 1, 'delete' => false])->create();

        $response = $this->post('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/user');

        $response->assertOk();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/auth/logout');

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_deleted_user_cant_access(): void
    {
        $admin = User::factory()->state(['delete' => true])->create();

        $response = $this->post('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/user');

        $response->assertUnauthorized();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/auth/logout');

        $response->assertOk();
    }
}
