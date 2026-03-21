<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use Modules\Users\Models\User;

class AuthTest extends TestCase
{
    public function test_user_can_register(): void
    {
        $response = $this->post('/api/v1/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@kirpi.dev',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->assertResponseStatus($response, 201);
    }

    public function test_user_can_login(): void
    {
        // Kullanıcı oluştur
        User::create([
            'name'     => 'Login Test',
            'email'    => 'login@kirpi.dev',
            'password' => 'secret123',
        ]);

        $response = $this->post('/api/v1/auth/login', [
            'email'    => 'login@kirpi.dev',
            'password' => 'secret123',
        ]);

        $this->assertResponseOk($response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'name'     => 'Wrong Pass',
            'email'    => 'wrong@kirpi.dev',
            'password' => 'correct_password',
        ]);

        $response = $this->post('/api/v1/auth/login', [
            'email'    => 'wrong@kirpi.dev',
            'password' => 'wrong_password',
        ]);

        $this->assertResponseStatus($response, 401);
    }

    public function test_protected_route_requires_auth(): void
    {
        $response = $this->get('/api/v1/me');
        $this->assertResponseStatus($response, 401);
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::create([
            'name'     => 'Auth User',
            'email'    => 'auth@kirpi.dev',
            'password' => 'secret123',
        ]);

        // JWT token al
        $jwtGuard = \Core\Auth\Facades\Auth::guard('api');
        $tokens   = $jwtGuard->issueTokens($user);

        $response = $this->get('/api/v1/me', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ]);

        $this->assertResponseOk($response);
    }

    public function test_validation_fails_with_invalid_email(): void
    {
        $response = $this->post('/api/v1/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'secret123',
        ]);

        $this->assertResponseStatus($response, 422);
    }

    public function test_database_has_user_after_creation(): void
    {
        User::create([
            'name'  => 'DB Test',
            'email' => 'dbtest@kirpi.dev',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'dbtest@kirpi.dev']);
    }
}