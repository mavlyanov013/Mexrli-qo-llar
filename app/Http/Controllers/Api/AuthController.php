<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'message' => 'Ro‘yxatdan o‘tish muvaffaqiyatli bajarildi',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Login yoki parol xato',
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = auth('api')->user();

        if ($user && $user->token()) {
            $user->token()->revoke();
        }

        return response()->json([
            'message' => 'Tizimdan chiqildi',
        ]);
    }
}
