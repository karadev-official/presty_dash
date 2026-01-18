<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\v1\RegisterRequest;
use App\Models\Resource;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Presty Dash API',
    description: 'API documentation',
    contact: new OA\Contact(
        name: 'Support Presty',
        email: 'support@presty.app'
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Development Server"
)]
#[OA\Server(
    url: "https://docs.presty.app",
    description: "Production Server"
)]
class AuthController extends Controller
{

    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: 'User registration',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'register_role', 'device_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'yourpassword'),
                    new OA\Property(property: 'register_role', type: 'string', example: 'customer'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'My iPhone'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful registration',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'token', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - wrong register role'
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'User login',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'device_name'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'yourpassword'),
                    new OA\Property(property: 'login_role', type: 'string', example: 'customer'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'My iPhone'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'token', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - wrong login role'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: 'Get authenticated user info',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of user info',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                        new OA\Property(property: 'role', type: 'string', example: 'customer'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T12:00:00Z'),
                        new OA\Property(property: 'specialty', type: 'string', example: 'Coiffure'),
                        new OA\Property(property: 'avatar_url', type: 'string', format: 'uri', example: 'https://example.com/avatars/johndoe.jpg'),
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/auth/logout',
        summary: 'User logout',
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful logout',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'ok', type: 'boolean', example: true),
                    ]
                )
            ),
        ]
    )]
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
            'avatar_url' => $user->avatar_url,
            'email' => $user->email,
            'role' => $user->roles->first()?->name ?? null,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
