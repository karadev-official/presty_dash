<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\v1\RegisterRequest;
use App\Models\Resource;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
//        return response()->json(['data' => $request->all()], 201);
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
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
        if ($data['register_role'] === 'pro') {
            $user->ProfessionalProfile()->create([
                'specialty' => $data['specialty'] ?? null
            ]);
            if(!$user->defaultResource()->exists()){
                $user->resources()->create([
                    'name' => $user->name ?? 'Moi',
                    'specialty' => '',
                    'type' => Resource::TYPE_SELF,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }
        }

        $token = $user->createToken($data['device_name'])->plainTextToken;
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }


    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Les informations de connexion sont invalides.'
            ], 401);
        }

        if (isset($data['login_role']) && !$user->hasRole($data['login_role'])) {
            return response()->json([
                'message' => 'Vous tentez de vous connecter dans le mauvais espace (client/professionnel).'
            ], 403);
        }

        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
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
                    'type' => Resource::TYPE_SELF,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }
            $professionalProfile = $user->professionalProfile;
            if(!$professionalProfile){
                $user->ProfessionalProfile()->create([
                    'specialty' => $user->specialty ?? null
                ]);
            }
        }
        return response()->json(new UserResource($user->fresh()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'ok' => true
        ]);
    }
}
