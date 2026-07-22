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
     * F16 — Tài khoản & phân quyền.
     * Tự đăng ký chỉ dành cho patient/family_member. Tài khoản mới ở trạng thái
     * "pending" cho tới khi xác minh email — không active ngay để tránh tạo
     * tài khoản rác truy cập dữ liệu y tế.
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

        // TODO: bắn event gửi email xác minh (Laravel Notification) — chưa nằm trong phạm vi bước này.

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

        // Thu hồi token cũ cùng thiết bị để tránh tích lũy token chết.
        $user->tokens()->where('name', $validated['device_name'])->delete();

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

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