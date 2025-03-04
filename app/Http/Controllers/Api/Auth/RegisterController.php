<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required']
        ]);

        $usernameExists = User::whereUsername($request->username)->exists();
        if ($usernameExists) {
            return $this->jsonResponse(
                status: 400,
                message: 'Username already exists',
            );
        }

        $user = new User;
        $user->username = $data['username'];
        $user->password = bcrypt($data['password']);
        $user->save();

        return $this->jsonResponse(
            message: 'Registration successful',
        );
    }
}
