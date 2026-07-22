<?php

namespace Tests\Feature;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->createActivePatient();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'WrongPass1',
            'device_name' => 'test-device',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
    }

    public function test_login_fails_for_suspended_or_deactivated_user(): void
    {
        foreach (['suspended', 'deactivated'] as $status) {
            $user = $this->createActivePatient(['status' => $status]);

            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'SecurePass1',
                'device_name' => 'test-device',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'email' => 'Tài khoản đã bị khóa. Liên hệ quản trị viên để được hỗ trợ.',
                ]);
        }
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = $this->createActivePatient();
        $token = $this->loginAndGetToken($user);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) Str::before($token, '|'),
        ]);

        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_login_deletes_previous_token_with_same_device_name(): void
    {
        $user = $this->createActivePatient();

        $this->loginAndGetToken($user, 'same-device');
        $this->loginAndGetToken($user, 'same-device');

        $this->assertSame(1, $user->tokens()->where('name', 'same-device')->count());
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = $this->createActivePatient();
        $token = $this->loginAndGetToken($user);

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('roles.0', 'patient')
            ->assertJsonStructure(['id', 'name', 'email', 'status', 'roles']);
    }

    public function test_protected_auth_endpoints_reject_anonymous_requests(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    private function createActivePatient(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('SecurePass1'),
            'status' => 'active',
        ], $attributes));
        $user->assignRole('patient');

        return $user;
    }

    private function loginAndGetToken(User $user, string $deviceName = 'test-device'): string
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'SecurePass1',
            'device_name' => $deviceName,
        ])->assertOk()->json('token');
    }
}
