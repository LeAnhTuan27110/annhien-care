<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Encapsulates authentication persistence and token lifecycle operations. */
class AuthService implements AuthServiceInterface
{
    public function register(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
                'status' => 'pending',
            ]);

            // The request validator limits self-registration to end-user roles.
            $user->assignRole($attributes['role']);

            return $user;
        });
    }

    public function login(array $credentials): array
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        if ($user->status === 'suspended' || $user->status === 'deactivated') {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản đã bị khóa. Liên hệ quản trị viên để được hỗ trợ.'],
            ]);
        }

        // Maintain one active token per named client installation.
        $user->tokens()->where('name', $credentials['device_name'])->delete();

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        // Record successful authentication for account security and audit purposes.
        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'token' => $token,
            'user' => $this->getCurrentUser($user),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function getCurrentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $user->getRoleNames(),
        ];
    }
}
