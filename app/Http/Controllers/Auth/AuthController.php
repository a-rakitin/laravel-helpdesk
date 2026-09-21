<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\OpenApi\Auth\AuthResponseExamples;
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
    #[Response(status: 201, description: 'Registered customer and access token', examples: [AuthResponseExamples::AUTHENTICATED_USER])]
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

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
    #[Response(status: 200, description: 'Authenticated user and new access token', examples: [AuthResponseExamples::AUTHENTICATED_USER])]
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

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
    #[Response(status: 200, description: 'Authenticated user', examples: [AuthResponseExamples::CURRENT_USER])]
    public function me(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($request->user()),
        ]);
    }

    #[Endpoint(title: 'Logout', description: 'Logs out the authenticated user by revoking the current Sanctum access token.')]
    #[Response(status: 200, description: 'Logged out')]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
