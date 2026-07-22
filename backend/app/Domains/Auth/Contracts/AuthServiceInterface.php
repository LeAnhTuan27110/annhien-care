<?php

namespace App\Domains\Auth\Contracts;

use App\Models\User;

/** Defines the application-level authentication operations used by HTTP clients. */
interface AuthServiceInterface
{
    /** Creates a pending end-user account and assigns its permitted role. */
    public function register(array $attributes): User;

    /** Authenticates an account and returns a new token with API-safe user data. */
    public function login(array $credentials): array;

    /** Revokes the access token associated with the current authenticated user. */
    public function logout(User $user): void;

    /** Returns the API-safe representation of an authenticated user. */
    public function getCurrentUser(User $user): array;
}
