<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @unauthenticated
     */
    #[Endpoint(title: 'Register', description: 'Creates a customer account and returns a Sanctum bearer token.')]
    #[Response(
        status: 201,
        description: 'The newly registered customer and access token.',
        examples: [[
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'customer',
                'created_at' => '2026-08-30T11:00:00.000000Z',
                'updated_at' => '2026-08-30T11:00:00.000000Z',
            ],
            'token' => '1|aB3dEfGhIjKlMnOpQrStUvWxYz0123456789AbCd',
        ]],
    )]
    #[BodyParameter('name', description: 'Customer display name.', example: 'John Doe')]
    #[BodyParameter('email', description: 'Unique customer email address.', example: 'john@example.com')]
    #[BodyParameter('password', description: 'User password.', format: 'password', example: 'password123')]
    #[BodyParameter('password_confirmation', description: 'Must match the password field.', format: 'password', example: 'password123')]
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
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
    #[Endpoint(title: 'Login', description: 'Authenticates a user, revokes all existing API tokens, and returns a new Sanctum bearer token.')]
    #[Response(
        status: 200,
        description: 'The authenticated user and new access token.',
        examples: [[
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'customer',
                'created_at' => '2026-08-30T11:00:00.000000Z',
                'updated_at' => '2026-08-30T11:00:00.000000Z',
            ],
            'token' => '1|aB3dEfGhIjKlMnOpQrStUvWxYz0123456789AbCd',
        ]],
    )]
    #[BodyParameter('email', description: 'Registered user email address.', example: 'john@example.com')]
    #[BodyParameter('password', description: 'User password.', format: 'password', example: 'password123')]
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token,
        ]);
    }

    #[Endpoint(title: 'Current user', description: 'Returns the authenticated user.')]
    #[Response(
        status: 200,
        description: 'The authenticated user.',
        examples: [[
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'customer',
                'created_at' => '2026-08-30T11:00:00.000000Z',
                'updated_at' => '2026-08-30T11:00:00.000000Z',
            ],
        ]],
    )]
    public function me(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($request->user()),
        ]);
    }

    #[Endpoint(title: 'Logout', description: 'Logs out the authenticated user by revoking the current Sanctum access token.')]
    #[Response(status: 200, description: 'Logged out.')]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
