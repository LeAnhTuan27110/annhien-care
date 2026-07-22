<?php

namespace Tests\Feature;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('patient');
        Role::findOrCreate('family_member');
    }

    public function test_auth_service_contract_resolves_to_the_auth_service(): void
    {
        $this->assertInstanceOf(AuthService::class, $this->app->make(AuthServiceInterface::class));
    }

    public function test_registration_requires_valid_input(): void
    {
        $response = $this->postJson('/api/register', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_user_can_register(): void
    {
        $payload = [
            'name' => 'Nguyen Van An',
            'email' => 'an@example.com',
            'phone' => '0901234567',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
            'role' => 'patient',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('user.name', $payload['name'])
            ->assertJsonPath('user.email', $payload['email'])
            ->assertJsonPath('user.status', 'pending')
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'status']]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'status' => 'pending',
        ]);

        $this->assertTrue(User::where('email', $payload['email'])->firstOrFail()->hasRole('patient'));
    }

    public function test_login_requires_credentials_and_device_name(): void
    {
        $response = $this->postJson('/api/login', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'an@example.com',
            'password' => Hash::make('SecurePass1'),
            'status' => 'active',
        ]);
        $user->assignRole('patient');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'SecurePass1',
            'device_name' => 'test-device',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.status', 'active')
            ->assertJsonPath('user.roles.0', 'patient')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'status', 'roles']]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-device',
        ]);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_protected_auth_endpoints_reject_anonymous_requests(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }
}
