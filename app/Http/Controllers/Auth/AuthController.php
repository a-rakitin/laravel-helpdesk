<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @unauthenticated
     */
    #[Endpoint(
        title: 'Register',
        description: 'Creates a customer account and returns a Sanctum bearer token. Auth responses intentionally keep the Postman-friendly shape { user, token }, with user normalized by UserResource. Validation errors are returned for missing fields, invalid or duplicate email addresses, weak passwords, and password confirmation mismatches.'
    )]
    #[BodyParameter(
        'password_confirmation',
        description: 'Must match password.',
        required: true,
        example: 'password123'
    )]
    public function register(Request $request)
    {
        $data = $request->validate([
            /**
             * Customer display name.
             *
             * @example John Doe
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Unique customer email address.
             *
             * @example john@example.com
             */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            /**
             * Password with at least 8 characters. Must be confirmed by password_confirmation.
             *
             * @example password123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ])->refresh();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token,
        ], 201);
    }

    /**
     * @unauthenticated
     */
    #[Endpoint(
        title: 'Login',
        description: 'Authenticates a user, revokes previous API tokens for that user, and returns a new Sanctum bearer token. Auth responses intentionally keep the Postman-friendly shape { user, token }, with user normalized by UserResource. Invalid credentials return a 422 validation response on the email field.'
    )]
    public function login(Request $request)
    {
        $data = $request->validate([
            /**
             * Registered user email address.
             *
             * @example qa-agent@example.com
             */
            'email' => ['required', 'string', 'email'],

            /**
             * User password.
             *
             * @example password
             */
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // optional: revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token,
        ]);
    }

    #[Endpoint(
        title: 'Current user',
        description: 'Returns the authenticated user for a valid Sanctum bearer token. This auth response intentionally keeps the shape { user } instead of { data } so the existing Postman demo flow remains stable.'
    )]
    public function me(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($request->user()),
        ]);
    }

    #[Endpoint(
        title: 'Logout',
        description: 'Deletes the current Sanctum access token. The same bearer token cannot be used on protected endpoints after a successful logout.'
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
