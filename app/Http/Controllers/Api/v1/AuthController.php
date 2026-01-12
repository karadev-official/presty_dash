<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\v1\RegisterRequest;
use App\Models\Resource;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'specialty' => $data['register_role'] === 'pro' ? ($data['specialty'] ?? null) : null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $authorizeRole = ['customer', 'pro'];

        if (!in_array($data['register_role'], $authorizeRole)) {
            return response()->json([
                'message' => 'Une erreur est survenue, veuillez nous contacter si le problème persiste.'
            ], 403);
        }

        $user->assignRole($data['register_role']);

        // ✅ Si c’est un pro : créer la ressource self tout de suite
        if ($data['register_role'] === 'pro' && !$user->defaultResource()->exists()) {
            $user->resources()->create([
                'name' => $user->name ?? 'Moi',
                'specialty' => $user->specialty,   // ✅ reprend la specialty du pro
                'type' => Resource::TYPE_SELF,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        $token = $user->createToken($data['device_name'])->plainTextToken;
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

        if (isset($credentials['login_role']) && !$user->hasRole($credentials['login_role'])) {
            return response()->json([
                'message' => 'Vous tentez de vous connecter dans le mauvais espace (client/professionnel).'
            ], 403);
        }

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if ($user->hasRole('pro')) {
            $self = $user->defaultResource()->first();

            if (!$self) {
                $user->resources()->create([
                    'name' => $user->name ?? 'Moi',
                    'specialty' => $user->specialty,
                    'type' => Resource::TYPE_SELF,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }
        }
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
            'specialty' => $user->specialty,
            'email' => $user->email,
            'role' => $user->roles->first()?->name ?? null,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
