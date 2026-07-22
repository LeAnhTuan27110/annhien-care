<?php

namespace App\Domains\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validates the credentials and client device identifier for an API login. */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Login is a public endpoint; credential validation happens in the controller.
        return true;
    }

    public function rules(): array
    {
        // The device name scopes token replacement to one client installation.
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string'], // Used as the Sanctum token name, e.g. "flutter-app" or "web".
        ];
    }
}
