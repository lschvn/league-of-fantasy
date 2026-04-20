<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * register a new api user.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->except(['device_name']));
        $user->load('memberships.fantasyTeam');

        $token = $user->createToken($request->input('device_name', 'api-token'))->plainTextToken;

        return $this->successResponse('account created successfully.', [
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * issue a personal access token for an existing user.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return $this->unprocessableResponse('invalid credentials.');
        }

        $user->load('memberships.fantasyTeam');
        $token = $user->createToken($request->input('device_name', 'api-token'))->plainTextToken;

        return $this->successResponse('logged in successfully.', [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * revoke the current personal access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse('logged out successfully.');
    }

    /**
     * return the authenticated user and current memberships.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('memberships.fantasyTeam');

        return $this->successResponse(
            'authenticated user fetched successfully.',
            new UserResource($user)
        );
    }
}
