<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Handles HTTP requests while the Auth service owns persistence and business logic. */
class AuthController
{
    public function __construct(private readonly AuthServiceInterface $authService)
    {
    }

    /** Registers a permitted end-user role and returns its pending account. */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        event(new Registered($user));
        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng xác minh email trước khi đăng nhập.',
            'user' => $user->only(['id', 'name', 'email', 'status']),
        ], 201);
    }

    /** Validates credentials and delegates token creation to the Auth service. */
    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json($this->authService->login($request->validated()));
    }

    /** Revokes the token used by the current authenticated request. */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Đã đăng xuất.']);
    }

    /** Returns the allowlisted representation of the current authenticated user. */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->authService->getCurrentUser($request->user()));
    }
}
