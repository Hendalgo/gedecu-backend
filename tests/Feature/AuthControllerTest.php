<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

class AuthControllerTest extends TestCase
{
    protected $authController;

    public function setUp(): void
    {
        parent::setUp();
        $this->authController = new AuthController();
    }

    public function testLoginWithValidCredentials(): void
    {
        $request = new Request();
        $request->replace([
            'email' => 'admin@gedecu.com',
            'password' => 'password'
        ]);

        $response = $this->authController->login($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $request = new Request();
        $request->replace([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response = $this->authController->login($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testLoginWithMissingCredentials(): void
    {
        $request = new Request();
        $request->replace([
            'email' => 'admin@gedecu.com',
        ]);

        $response = $this->authController->login($request);

        $this->assertEquals(422, $response->getStatusCode());
    }
}