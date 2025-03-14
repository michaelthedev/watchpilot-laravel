<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

final class LoginController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $credentials = $request->only('username', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return $this->jsonResponse(
                status: 401,
                message: 'Invalid credentials'
            );
        }

        return $this->jsonResponse(
            message: 'Login successful',
            data: $this->buildJWTData($token)
        );
    }

    private function buildJWTData(string $token): array
    {
        // set custom ttl
        JWTAuth::factory()->setTTL(60 * 24 * 7);
        return [
            'token' => $token,
            'type' => 'bearer',
            'expiry' => JWTAuth::factory()->getTTL() * 60
        ];
    }
}
