<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController
{
    /**
     * F16 — Accounts and authorization.
     * Self-registration is limited to patients and family members. New accounts
     * remain "pending" until email verification to prevent spam accounts from
     * accessing medical data.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'status' => 'pending',
            ]);

            $user->assignRole($validated['role']);

            return $user;
        });

        // TODO: Dispatch an email-verification event (Laravel Notification); out of scope for this step.

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng xác minh email trước khi đăng nhập.',
            'user' => $user->only(['id', 'name', 'email', 'status']),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        if ($user->status === 'suspended' || $user->status === 'deactivated') {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản đã bị khóa. Liên hệ quản trị viên để được hỗ trợ.'],
            ]);
        }

        // Revoke the existing token for this device to avoid accumulating stale tokens.
        $user->tokens()->where('name', $validated['device_name'])->delete();

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }
    //delete
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đã đăng xuất.']);
    }
    
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
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
