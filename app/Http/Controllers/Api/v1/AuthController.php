<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\v1\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        $credentials = $request->only('email', 'name', 'register_role', 'password', 'device_name');
        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
        ]);

        $token = $user->createToken($credentials['device_name'])->plainTextToken;
        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $request->validated();
        $credentials = $request->only('email', 'password', 'login_role', 'device_name');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Les informations de connexion sont invalides.'
            ], 401);
        }

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'ok' => true
        ]);
    }

    public function userPayload(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
