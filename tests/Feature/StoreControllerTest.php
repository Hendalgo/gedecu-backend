<?php

namespace Tests\Feature;

use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_interacting_without_authenticate(): void
    {
        $response = $this->get('/api/stores');

        $response->assertUnauthorized();

        $response = $this->post('/api/stores');
        $response->assertUnauthorized();

        $response = $this->put('/api/stores/1');
        $response->assertUnauthorized();

        $response = $this->delete('/api/stores/1');
        $response->assertUnauthorized();
    }
    /* public function test_admin_user_can_create_and_delete_stores(): void
    {
        $admin = \App\Models\User::factory()->state(['role_id' => 1])->create();

        // Genera el token
        $token = $admin->createToken('testToken')->plainTextToken;
        error_log($token);
        // Usa el token en las solicitudes
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/stores');

        $response->assertOk();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->delete('/api/stores/1');

        $response->assertOk();
    }

    public function test_non_admin_user_cannot_create_or_delete_stores(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post('/api/stores');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->delete('/api/stores/1');
        $response->assertStatus(403);
    }

    public function test_all_users_can_get_stores(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/api/stores');
        $response->assertOk();
    } */
}
